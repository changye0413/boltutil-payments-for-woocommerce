<?php
defined( 'ABSPATH' ) || exit;

/** Signed, replay-resistant BoltUtil payment events for WooCommerce orders. */
final class BoltUtil_WC_Webhook {
    public static function register_route() {
        // BoltUtil needs a public URL; receive() authenticates every event by
        // HMAC before any order state is changed.
        register_rest_route( 'boltutil/v1', '/webhook', array(
            'methods'             => 'POST',
            'callback'            => array( __CLASS__, 'receive' ),
            'permission_callback' => '__return_true',
        ) );
    }

    public static function receive( $request ) {
        $raw       = $request->get_body();
        $timestamp = $request->get_header( 'x-bolt-webhook-timestamp' );
        $signature = $request->get_header( 'x-bolt-webhook-signature' );
        if ( ! is_string( $raw ) || strlen( $raw ) > 65536 || ! preg_match( '/^[0-9]{13}$/', (string) $timestamp ) ||
            ! preg_match( '/^[a-f0-9]{64}$/i', (string) $signature ) ||
            abs( floor( microtime( true ) * 1000 ) - (float) $timestamp ) > 300000 ) {
            return new WP_Error( 'boltutil_invalid_webhook', 'Invalid webhook headers.', array( 'status' => 401 ) );
        }
        $event = json_decode( $raw, true );
        if ( ! is_array( $event ) || ! preg_match( '/^evt_[a-f0-9]{32}$/', $event['eventId'] ?? '' ) ||
            ! in_array( $event['type'] ?? '', array( 'payment.completed', 'payment.expired', 'payment.underpaid', 'payment.overpaid' ), true ) ||
            ! isset( $event['data'] ) || ! is_array( $event['data'] ) ) {
            return new WP_Error( 'boltutil_invalid_webhook', 'Invalid webhook event.', array( 'status' => 400 ) );
        }
        $data = $event['data'];
        $payment_id = $data['paymentId'] ?? '';
        $external   = $data['externalOrderId'] ?? '';
        if ( ! is_string( $payment_id ) || ! preg_match( '/^[a-f0-9]{32}$/i', $payment_id ) ||
            ! is_string( $external ) || ! preg_match( '/^wc_[a-f0-9]{12}_[1-9][0-9]*$/', $external ) ) {
            return new WP_Error( 'boltutil_invalid_webhook', 'Invalid payment reference.', array( 'status' => 400 ) );
        }
        $gateway = new BoltUtil_WC_Gateway();
        // Verify against the configured merchant's saved secret and raw body.
        $secret = BoltUtil_WC_API::unseal( $gateway->get_option( 'live_webhook_secret', '' ) );
        if ( '' === $secret || ! hash_equals( hash_hmac( 'sha256', $timestamp . '.' . $raw, $secret ), strtolower( $signature ) ) ) {
            return new WP_Error( 'boltutil_invalid_signature', 'Invalid webhook signature.', array( 'status' => 401 ) );
        }
        $parts = explode( '_', $external );
        $order = wc_get_order( (int) $parts[2] );
        if ( ! $order || 'boltutil_usdt' !== $order->get_payment_method() ||
            $external !== BoltUtil_WC_Gateway::external_id( $order ) ||
            $payment_id !== $order->get_meta( '_boltutil_payment_id', true ) ||
            $external !== $order->get_meta( '_boltutil_external_id', true ) ) {
            // The create response may still be committing into Woo order meta.
            return new WP_Error( 'boltutil_order_not_ready', 'Payment is not linked to this order.', array( 'status' => 503 ) );
        }
        $mode    = $order->get_meta( '_boltutil_mode', true );
        if ( 'live' !== $mode ) {
            return new WP_Error( 'boltutil_mode_mismatch', 'Payment environment mismatch.', array( 'status' => 422 ) );
        }
        if ( isset( $data['environment'] ) && 'LIVE' !== $data['environment'] ) {
            return new WP_Error( 'boltutil_mode_mismatch', 'Live event environment mismatch.', array( 'status' => 422 ) );
        }
        if ( $order->get_meta( '_boltutil_network', true ) !== ( $data['network'] ?? '' ) ||
            ! BoltUtil_WC_Gateway::decimal_equal( $order->get_meta( '_boltutil_amount', true ), $data['amount'] ?? '' ) ||
            'USDT' !== ( $data['token'] ?? ( $data['currency'] ?? '' ) ) ) {
            return new WP_Error( 'boltutil_payment_mismatch', 'Webhook payment fields mismatch.', array( 'status' => 422 ) );
        }

        try {
            // This signed merchant-scoped read confirms ownership and finality.
            $payment = ( new BoltUtil_WC_API( $gateway->settings ) )->get_payment( $payment_id );
        } catch ( Throwable $error ) {
            return new WP_Error( 'boltutil_lookup_unavailable', 'Payment lookup temporarily unavailable.', array( 'status' => 503 ) );
        }
        if ( ! BoltUtil_WC_Gateway::matches_order( $order, $payment, $order->get_meta( '_boltutil_network', true ),
            $mode, $external ) ||
            ! BoltUtil_WC_Gateway::decimal_equal( $order->get_meta( '_boltutil_amount', true ), $payment['amount'] ?? '' ) ) {
            return new WP_Error( 'boltutil_payment_mismatch', 'Verified payment does not match the order.', array( 'status' => 422 ) );
        }
        if ( 'payment.completed' === $event['type'] && 'COMPLETED' !== ( $payment['status'] ?? '' ) ) {
            return new WP_Error( 'boltutil_not_final', 'Payment is not confirmed yet.', array( 'status' => 503 ) );
        }

        $claim = self::claim( $event['eventId'], $order->get_id(), $payment_id );
        if ( $claim instanceof WP_REST_Response ) {
            return $claim;
        }
        if ( ! $claim ) {
            return new WP_Error( 'boltutil_event_busy', 'Event is being processed.', array( 'status' => 503 ) );
        }
        try {
            if ( 'payment.completed' === $event['type'] && ! $order->is_paid() ) {
                $txid = isset( $payment['txHash'] ) && is_string( $payment['txHash'] ) ? sanitize_text_field( $payment['txHash'] ) : '';
                $order->payment_complete( $txid );
                $order->add_order_note( boltutil_wc_text( 'BoltUtil 已确认这笔 USDT 付款。', __( 'BoltUtil confirmed this USDT payment.', 'boltutil-payments-for-woocommerce' ) ) );
                $order->save();
            } elseif ( 'payment.completed' !== $event['type'] && ! $order->is_paid() &&
                ! BoltUtil_WC_Gateway::record_terminal_status( $order, $payment['status'] ?? '' ) ) {
                /* translators: %s is the BoltUtil payment status, such as EXPIRED or UNDERPAID. */
                $status_message = __( 'BoltUtil payment status: %s.', 'boltutil-payments-for-woocommerce' );
                $order->add_order_note( sprintf( boltutil_wc_text( 'BoltUtil 支付状态：%s。', $status_message ), sanitize_text_field( $payment['status'] ?? '' ) ) );
                $order->save();
            }
            self::finish( $event['eventId'] );
            return new WP_REST_Response( array( 'status' => 'SUCCESS' ), 200 );
        } catch ( Throwable $error ) {
            self::release( $event['eventId'] );
            return new WP_Error( 'boltutil_processing_failed', 'Payment processing temporarily failed.', array( 'status' => 503 ) );
        }
    }

    private static function table() {
        global $wpdb;
        return $wpdb->prefix . 'boltutil_events';
    }

    private static function claim( $event_id, $order_id, $payment_id ) {
        global $wpdb;
        $table = self::table();
        $now = gmdate( 'Y-m-d H:i:s' );
        // The event table is the atomic replay guard; an object cache cannot replace these writes.
        $wpdb->query( $wpdb->prepare(
            "INSERT IGNORE INTO %i (event_id, order_id, payment_id, status, created_at) VALUES (%s, %d, %s, 'NEW', %s)",
            $table, $event_id, $order_id, $payment_id, $now
        ) );
        $updated = $wpdb->query( $wpdb->prepare(
            "UPDATE %i SET status = 'PROCESSING', lease_until = %s WHERE event_id = %s AND order_id = %d AND payment_id = %s AND (status = 'NEW' OR (status = 'PROCESSING' AND lease_until < %s))",
            $table, gmdate( 'Y-m-d H:i:s', time() + 120 ), $event_id, $order_id, $payment_id, $now
        ) );
        if ( 1 === $updated ) {
            return true;
        }
        $status = $wpdb->get_var( $wpdb->prepare( "SELECT status FROM %i WHERE event_id = %s AND order_id = %d AND payment_id = %s", $table, $event_id, $order_id, $payment_id ) );
        if ( 'DONE' === $status ) {
            return new WP_REST_Response( array( 'status' => 'SUCCESS' ), 200 );
        }
        return false;
    }

    private static function finish( $event_id ) {
        global $wpdb;
        if ( 1 !== $wpdb->update( self::table(), array( 'status' => 'DONE', 'lease_until' => null, 'processed_at' => gmdate( 'Y-m-d H:i:s' ) ), array( 'event_id' => $event_id, 'status' => 'PROCESSING' ) ) ) {
            throw new RuntimeException( 'Could not complete BoltUtil event record.' );
        }
    }

    private static function release( $event_id ) {
        global $wpdb;
        $wpdb->update( self::table(), array( 'status' => 'NEW', 'lease_until' => null ), array( 'event_id' => $event_id, 'status' => 'PROCESSING' ) );
    }
}
