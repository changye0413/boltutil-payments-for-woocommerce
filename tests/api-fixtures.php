<?php
/** Actual API adapter with intercepted WordPress HTTP; never contacts a service. */
define( 'ABSPATH', __DIR__ );
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function wp_http_validate_url( $url ) { return $url; }
function wp_salt( $scheme ) { return 'local-fixture-salt-' . $scheme; }
function wp_json_encode( $value, $flags = 0 ) { return json_encode( $value, $flags ); }
function is_wp_error( $response ) { return $response instanceof Fixture_HTTP_Error; }
function wp_remote_retrieve_response_code( $response ) { return $response['status']; }
function wp_remote_retrieve_body( $response ) { return $response['body']; }
class Fixture_HTTP_Error {}
function wp_safe_remote_request( $url, $options ) {
    $GLOBALS['requests'][] = array( $url, $options );
    return array_shift( $GLOBALS['responses'] );
}
require __DIR__ . '/../includes/class-boltutil-wc-api.php';
$checks = 0;
function check_api( $condition, $label ) {
    ++$GLOBALS['checks'];
    if ( ! $condition ) { throw new RuntimeException( $label ); }
    echo $label . ": OK\n";
}
function rejected_api( $action, $label ) {
    try { $action(); } catch ( RuntimeException $error ) { check_api( true, $label ); return; }
    throw new RuntimeException( $label . ' did not reject' );
}
function response_api( $status, $data ) {
    return array( 'status' => $status, 'body' => json_encode( array( 'code' => 200, 'data' => $data ) ) );
}
$key = 'bt_live_' . str_repeat( 'x', 24 ); // Synthetic, excluded from installable ZIP.
$secret = 'local-fixture-signing-secret';
$sealed = BoltUtil_WC_API::seal( $key );
check_api( $sealed !== $key && BoltUtil_WC_API::unseal( $sealed ) === $key, 'Credential encryption round trip' );
check_api( BoltUtil_WC_API::seal( $key ) !== $sealed, 'Fresh encryption nonce' );
$bytes = base64_decode( $sealed ); $bytes[28] = chr( ord( $bytes[28] ) ^ 1 );
check_api( '' === BoltUtil_WC_API::unseal( base64_encode( $bytes ) ), 'Tampered credential cannot decrypt' );
check_api( '' === BoltUtil_WC_API::unseal( 'not-base64' ), 'Malformed credential fails closed' );
$api = new BoltUtil_WC_API( array( 'live_api_key' => $sealed, 'live_webhook_secret' => BoltUtil_WC_API::seal( $secret ), 'api_url' => 'https://example.invalid' ) );
$GLOBALS['requests'] = array(); $GLOBALS['responses'] = array( response_api( 200, array( 'paymentId' => str_repeat( 'a', 32 ) ) ) );
$payload = array( 'amount' => '10.123', 'token' => 'USDC', 'network' => 'BASE', 'merchantReference' => '订单 / test' );
$api->create_payment( $payload, 'wc_123456789abc_42' );
list( $url, $options ) = $GLOBALS['requests'][0];
check_api( 'https://api.boltutil.com/api/v1/payments' === $url, 'Merchant-editable origin ignored' );
check_api( true === $options['sslverify'] && 0 === $options['redirection'], 'TLS verification enabled and redirects disabled' );
check_api( '10.123' === json_decode( $options['body'], true )['amount'], 'Exact decimal string retained' );
check_api( hash_hmac( 'sha256', $options['headers']['X-Bolt-Timestamp'] . '.' . $options['body'], $secret ) === $options['headers']['X-Bolt-Signature'], 'HMAC signs the exact transmitted body' );
check_api( 'wc_123456789abc_42' === $options['headers']['Idempotency-Key'], 'Creation retains idempotency key' );
$before = count( $GLOBALS['requests'] );
rejected_api( function () use ( $api ) { $api->get_payment( '../secret' ); }, 'Invalid payment ID rejected' );
rejected_api( function () use ( $api ) { $api->find_payment( 'bad&other=merchant' ); }, 'Invalid external ID rejected' );
check_api( $before === count( $GLOBALS['requests'] ), 'Malformed references never reach HTTP' );
$GLOBALS['responses'] = array( response_api( 200, array( 'status' => 'PENDING' ) ) );
$api->get_payment( str_repeat( 'a', 32 ) );
list( $url, $options ) = end( $GLOBALS['requests'] );
check_api( 'GET' === $options['method'] && null === $options['body'] && ! isset( $options['headers']['Idempotency-Key'] ), 'Status reads do not create or replay payments' );
foreach ( array( new Fixture_HTTP_Error(), array( 'status' => 200, 'body' => 'not-json' ), array( 'status' => 200, 'body' => '{"code":200,"data":null}' ), response_api( 503, array() ) ) as $response ) {
    $GLOBALS['responses'] = array( $response );
    rejected_api( function () use ( $api ) { $api->get_payment( str_repeat( 'a', 32 ) ); }, 'Transport and invalid responses fail closed' );
}
$GLOBALS['responses'] = array( response_api( 404, array() ), response_api( 200, array( 'TRC20', 'BASE' ) ) );
check_api( array( 'USDT', 'USDT' ) === array_column( $api->capabilities()['routes'], 'token' ), 'Only missing capabilities endpoint falls back to USDT' );
$GLOBALS['responses'] = array( response_api( 429, array() ) ); $before = count( $GLOBALS['requests'] );
rejected_api( function () use ( $api ) { $api->capabilities(); }, 'Rate limiting does not enable fallback routes' );
check_api( $before + 1 === count( $GLOBALS['requests'] ), 'No follow-up capability request after throttling' );
echo 'API fixtures passed: ' . $checks . " checks.\n";
