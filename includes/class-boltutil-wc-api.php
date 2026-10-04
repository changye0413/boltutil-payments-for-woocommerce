<?php
defined( 'ABSPATH' ) || exit;

/** Server-only adapter for the BoltUtil Public Payment API. */
final class BoltUtil_WC_API {
    const API_ORIGIN = 'https://api.boltutil.com';
    private $base_url;
    private $api_key;
    private $signing_secret;

    public function __construct( $settings ) {
        // The LIVE key must never be sent to a merchant-editable destination.
        $base = self::API_ORIGIN;
        $parts = wp_parse_url( $base );
        if ( ! is_array( $parts ) || ! wp_http_validate_url( $base ) ||
            'https' !== ( $parts['scheme'] ?? '' ) || ! empty( $parts['user'] ) ||
            ! empty( $parts['pass'] ) || ! empty( $parts['query'] ) || ! empty( $parts['fragment'] ) ||
            ! in_array( $parts['path'] ?? '', array( '', '/' ), true ) ) {
            throw new RuntimeException( 'BoltUtil API URL must be a public HTTPS URL.' );
        }
        $this->base_url = $base;
        $this->api_key = self::unseal( $settings['live_api_key'] ?? '' );
        $this->signing_secret = self::unseal( $settings['live_webhook_secret'] ?? '' );
        if ( ! preg_match( '/^bt_live_[A-Za-z0-9_-]+$/', $this->api_key ) || '' === $this->signing_secret ) {
            throw new RuntimeException( 'BoltUtil API credentials are not configured.' );
        }
    }

    public function capabilities() {
        try {
            return $this->request( 'GET', '/api/v1/payments/capabilities' );
        } catch ( RuntimeException $error ) {
            // Only an older server without the endpoint may fall back to USDT.
            if ( 404 !== $error->getCode() ) { throw $error; }
            $routes = array();
            foreach ( $this->networks() as $network ) {
                $routes[] = array( 'token' => 'USDT', 'network' => $network, 'decimals' => 6 );
            }
            return array( 'schemaVersion' => 1, 'routes' => $routes );
        }
    }

    public function networks() {
        return $this->request( 'GET', '/api/v1/payments/networks' );
    }

    public function create_payment( $payload, $idempotency_key ) {
        return $this->request( 'POST', '/api/v1/payments', $payload, $idempotency_key );
    }

    public function get_payment( $payment_id ) {
        if ( ! preg_match( '/^[a-f0-9]{32}$/i', $payment_id ) ) {
            throw new RuntimeException( 'Invalid BoltUtil payment ID.' );
        }
        return $this->request( 'GET', '/api/v1/payments/' . $payment_id );
    }

    public function find_payment( $external_id ) {
        if ( ! preg_match( '/^[A-Za-z0-9_-]{6,64}$/', $external_id ) ) {
            throw new RuntimeException( 'Invalid external order ID.' );
        }
        return $this->request( 'GET', '/api/v1/payments?externalOrderId=' . rawurlencode( $external_id ) );
    }

    private function request( $method, $target, $payload = null, $idempotency_key = '' ) {
        // BoltUtil signs the exact UTF-8 JSON body sent on the wire, prefixed by
        // the millisecond timestamp. Re-encoding after signing would break HMAC.
        $body      = null === $payload ? '' : wp_json_encode( $payload, JSON_UNESCAPED_SLASHES );
        $timestamp = (string) floor( microtime( true ) * 1000 );
        $message   = $timestamp . '.' . $body;
        $headers   = array(
            'Accept'           => 'application/json',
            'Content-Type'     => 'application/json',
            'X-Bolt-Key'       => $this->api_key,
            'X-Bolt-Timestamp' => $timestamp,
            'X-Bolt-Signature' => hash_hmac( 'sha256', $message, $this->signing_secret ),
        );
        if ( '' !== $idempotency_key ) {
            // Retrying the same WooCommerce order must reuse its original key.
            $headers['Idempotency-Key'] = $idempotency_key;
        }
        $response = wp_safe_remote_request( $this->base_url . $target, array(
            'method'      => $method,
            'headers'     => $headers,
            'body'        => 'POST' === $method ? $body : null,
            'timeout'     => 15,
            'redirection' => 0,
            'sslverify'   => true,
        ) );
        if ( is_wp_error( $response ) ) {
            throw new RuntimeException( 'BoltUtil is temporarily unavailable.' );
        }
        $status = wp_remote_retrieve_response_code( $response );
        $json   = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( $status < 200 || $status >= 300 || ! is_array( $json ) || 200 !== (int) ( $json['code'] ?? 0 ) ) {
            throw new RuntimeException( 'BoltUtil rejected the payment request (HTTP ' . (int) $status . ').', (int) $status );
        }
        if ( ! isset( $json['data'] ) || ! is_array( $json['data'] ) ) {
            throw new RuntimeException( 'BoltUtil returned an invalid response.' );
        }
        return $json['data'];
    }

    public static function seal( $plain ) {
        // Store credentials encrypted with this WordPress site's auth salts;
        // they are never embedded in frontend HTML or distributed ZIP files.
        if ( ! function_exists( 'openssl_encrypt' ) ) {
            throw new RuntimeException( 'OpenSSL is required to store BoltUtil credentials.' );
        }
        $key   = hash( 'sha256', wp_salt( 'auth' ) . wp_salt( 'secure_auth' ), true );
        $nonce = random_bytes( 12 );
        $tag   = '';
        $data  = openssl_encrypt( $plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag );
        if ( false === $data ) {
            throw new RuntimeException( 'Could not encrypt BoltUtil credentials.' );
        }
        return base64_encode( $nonce . $tag . $data );
    }

    public static function unseal( $sealed ) {
        if ( '' === $sealed || ! function_exists( 'openssl_decrypt' ) ) {
            return '';
        }
        $packed = base64_decode( $sealed, true );
        if ( false === $packed || strlen( $packed ) < 28 ) {
            return '';
        }
        $key = hash( 'sha256', wp_salt( 'auth' ) . wp_salt( 'secure_auth' ), true );
        $plain = openssl_decrypt( substr( $packed, 28 ), 'aes-256-gcm', $key, OPENSSL_RAW_DATA,
            substr( $packed, 0, 12 ), substr( $packed, 12, 16 ) );
        return false === $plain ? '' : $plain;
    }
}
