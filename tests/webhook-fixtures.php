<?php
/** Isolated behavioral checks for the signed WooCommerce Webhook receiver. */
define( 'ABSPATH', __DIR__ );

final class WP_Error {
    public $code;
    public $data;
    public function __construct( $code, $message, $data ) {
        $this->code = $code;
        $this->data = $data;
    }
}

final class WP_REST_Response {
    public $data;
    public $status;
    public function __construct( $data, $status ) {
        $this->data = $data;
        $this->status = $status;
    }
}

final class Fixture_Request {
    private $raw;
    private $headers;
    public function __construct( $event, $timestamp, $secret = 'fixture-secret' ) {
        $this->raw = json_encode( $event );
        $this->headers = array(
            'x-bolt-webhook-timestamp' => $timestamp,
            'x-bolt-webhook-signature' => hash_hmac( 'sha256', $timestamp . '.' . $this->raw, $secret ),
        );
    }
    public function get_body() { return $this->raw; }
    public function get_header( $name ) { return $this->headers[$name] ?? ''; }
}

final class Fixture_Order {
    public $paid = false;
    public $completions = 0;
    public $notes = array();
    public function get_id() { return 42; }
    public function get_total( $context = '' ) { return '1.00'; }
    public function get_payment_method() { return 'boltutil_usdt'; }
    public function get_meta( $key, $single = true ) {
        $meta = array(
            '_boltutil_payment_id' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            '_boltutil_external_id' => 'wc_123456789abc_42',
            '_boltutil_mode' => 'live',
            '_boltutil_network' => 'TRC20',
            '_boltutil_amount' => '1.000001',
        );
        return $meta[$key] ?? '';
    }
    public function is_paid() { return $this->paid; }
    public function payment_complete( $txid ) { $this->paid = true; ++$this->completions; }
    public function add_order_note( $note ) { $this->notes[] = $note; }
    public function save() {}
}

final class BoltUtil_WC_Gateway {
    public $settings = array();
    public function get_option( $name, $default = '' ) { return 'live_webhook_secret' === $name ? 'sealed' : $default; }
    public static function external_id( $order ) { return 'wc_123456789abc_' . $order->get_id(); }
    public static function decimal_equal( $left, $right ) { return (string) $left === (string) $right; }
    public static function matches_order( $order, $payment, $network, $mode, $external ) {
        return $payment['paymentId'] === $order->get_meta( '_boltutil_payment_id' ) &&
            $payment['externalOrderId'] === $external && $payment['network'] === $network &&
            $payment['token'] === 'USDT' && $payment['invoiceCurrency'] === 'USD' &&
            $payment['invoiceAmount'] === $order->get_total( 'edit' );
    }
    public static function record_terminal_status( $order, $status ) { return false; }
}

final class BoltUtil_WC_API {
    public static $fail = false;
    public static $payment;
    public static function unseal( $value ) { return 'fixture-secret'; }
    public function __construct( $settings ) {}
    public function get_payment( $id ) {
        if ( self::$fail ) { throw new RuntimeException( 'Fixture lookup failure' ); }
        return self::$payment;
    }
}

final class Fixture_DB {
    public $prefix = 'wp_';
    public $events = array();
    public $current = '';
    public function prepare( $sql, ...$args ) {
        $this->current = 0 === strpos( $sql, 'UPDATE %i' ) ? ( $args[2] ?? '' ) : ( $args[1] ?? '' );
        return $sql;
    }
    public function query( $sql ) {
        if ( 0 === strpos( $sql, 'INSERT IGNORE' ) ) {
            $this->events[$this->current] = $this->events[$this->current] ?? 'NEW';
            return 1;
        }
        if ( 0 === strpos( $sql, 'UPDATE' ) && 'NEW' === ( $this->events[$this->current] ?? '' ) ) {
            $this->events[$this->current] = 'PROCESSING';
            return 1;
        }
        return 0;
    }
    public function get_var( $sql ) { return $this->events[$this->current] ?? null; }
    public function update( $table, $values, $where ) {
        $this->events[$where['event_id']] = $values['status'];
        return 1;
    }
}

function wc_get_order( $id ) { return 42 === $id ? $GLOBALS['fixture_order'] : false; }
function sanitize_text_field( $value ) { return $value; }
function boltutil_wc_text( $chinese, $english ) { return $english; }
function __( $value, $domain ) { return $value; }

require __DIR__ . '/../includes/class-boltutil-wc-webhook.php';

function assert_code( $name, $actual, $expected ) {
    $code = $actual instanceof WP_Error ? $actual->code : ( $actual instanceof WP_REST_Response ? $actual->status : 'unknown' );
    if ( $code !== $expected ) {
        fwrite( STDERR, $name . ': expected ' . $expected . ', got ' . $code . "\n" );
        exit( 1 );
    }
    echo $name . ": OK\n";
}

$wpdb = new Fixture_DB();
$GLOBALS['fixture_order'] = new Fixture_Order();
$timestamp = (string) floor( microtime( true ) * 1000 );
$base = array(
    'eventId' => 'evt_11111111111111111111111111111111',
    'type' => 'payment.completed',
    'data' => array(
        'paymentId' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
        'externalOrderId' => 'wc_123456789abc_42',
        'environment' => 'LIVE',
        'network' => 'TRC20',
        'amount' => '1.000001',
        'token' => 'USDT',
    ),
);
BoltUtil_WC_API::$payment = array(
    'paymentId' => $base['data']['paymentId'],
    'externalOrderId' => $base['data']['externalOrderId'],
    'network' => 'TRC20',
    'amount' => '1.000001',
    'token' => 'USDT',
    'invoiceCurrency' => 'USD',
    'invoiceAmount' => '1.00',
    'status' => 'COMPLETED',
);

assert_code( 'bad HMAC', BoltUtil_WC_Webhook::receive( new Fixture_Request( $base, $timestamp, 'wrong-secret' ) ), 'boltutil_invalid_signature' );
assert_code( 'stale timestamp', BoltUtil_WC_Webhook::receive( new Fixture_Request( $base, (string) ( (int) $timestamp - 400000 ) ) ), 'boltutil_invalid_webhook' );
$changed = $base;
$changed['data']['externalOrderId'] = 'wc_ffffffffffff_42';
assert_code( 'order mismatch', BoltUtil_WC_Webhook::receive( new Fixture_Request( $changed, $timestamp ) ), 'boltutil_order_not_ready' );
$changed = $base;
$changed['data']['amount'] = '1.000002';
assert_code( 'amount mismatch', BoltUtil_WC_Webhook::receive( new Fixture_Request( $changed, $timestamp ) ), 'boltutil_payment_mismatch' );
BoltUtil_WC_API::$fail = true;
assert_code( 'merchant lookup unavailable', BoltUtil_WC_Webhook::receive( new Fixture_Request( $base, $timestamp ) ), 'boltutil_lookup_unavailable' );
BoltUtil_WC_API::$fail = false;
BoltUtil_WC_API::$payment['amount'] = '1.000002';
assert_code( 'verified payment mismatch', BoltUtil_WC_Webhook::receive( new Fixture_Request( $base, $timestamp ) ), 'boltutil_payment_mismatch' );
BoltUtil_WC_API::$payment['amount'] = '1.000001';
BoltUtil_WC_API::$payment['status'] = 'PENDING';
assert_code( 'not final', BoltUtil_WC_Webhook::receive( new Fixture_Request( $base, $timestamp ) ), 'boltutil_not_final' );
BoltUtil_WC_API::$payment['status'] = 'COMPLETED';
assert_code( 'verified completion', BoltUtil_WC_Webhook::receive( new Fixture_Request( $base, $timestamp ) ), 200 );
assert_code( 'duplicate event', BoltUtil_WC_Webhook::receive( new Fixture_Request( $base, $timestamp ) ), 200 );
if ( 1 !== $GLOBALS['fixture_order']->completions ) {
    fwrite( STDERR, "Completion was not exactly once.\n" );
    exit( 1 );
}
echo "payment_complete called once: OK\n";
