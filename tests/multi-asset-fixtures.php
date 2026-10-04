<?php
/** Exercise the actual gateway, including historic metadata defaults and server route filtering. */
define('ABSPATH', __DIR__);
define('MINUTE_IN_SECONDS', 60);
class WC_Payment_Gateway {
    public $settings = array();
    public function get_option($name, $default) { return $this->settings[$name] ?? $default; }
}
class BoltUtil_WC_API {
    public function __construct($settings) {}
    public function capabilities() { return $GLOBALS['capabilities']; }
}
function get_transient($key) { return false; }
function set_transient($key, $value, $seconds) {}
function wp_json_encode($value) { return json_encode($value); }
function sanitize_text_field($value) { return $value; }
function wp_unslash($value) { return $value; }
require __DIR__ . '/../includes/class-boltutil-wc-gateway.php';
class Asset_Order {
    public $meta = array();
    public function get_meta($key, $single) { return $this->meta[$key] ?? ''; }
    public function get_total($context) { return '1.00'; }
}
function check($condition, $name) {
    if (!$condition) throw new RuntimeException($name);
    echo $name . ": OK\n";
}
$gateway=(new ReflectionClass('BoltUtil_WC_Gateway'))->newInstanceWithoutConstructor();
$GLOBALS['capabilities']=array('routes'=>array(
    array('token'=>'USDT','network'=>'TRC20'), array('token'=>'USDC','network'=>'ERC20'),
    array('token'=>'USDC','network'=>'SOLANA'), array('token'=>'USDC','network'=>'BEP20','decimals'=>18), array('token'=>'UNKNOWN','network'=>'ERC20')
));
check(array_keys($gateway->available_routes())===array('USDT:TRC20'),'Upgrade defaults preserve USDT only');
$gateway->settings=array('tokens'=>array('USDC'),'networks'=>array('ERC20'));
check(array_keys($gateway->available_routes())===array('USDC:ERC20'),'Capabilities intersect token and network settings');
$select=new ReflectionMethod($gateway,'selected_route');
// PHP 7.4 needs explicit reflection access; PHP 8.1+ enables it by default.
if ( PHP_VERSION_ID < 80100 ) {
    $select->setAccessible(true);
}
$_POST=array('boltutil_route'=>'USDC:TRC20');
check(null===$select->invoke($gateway),'Forged unsupported route rejected');
$_POST=array('boltutil_route'=>'USDC:ERC20');
check('USDC'===$select->invoke($gateway)['token'],'Supported USDC route accepted');
$order=new Asset_Order();
$payment=array('paymentId'=>str_repeat('a',32),'externalOrderId'=>'wc_123456','network'=>'ERC20','token'=>'USDC','invoiceCurrency'=>'USD','invoiceAmount'=>'1');
check(!BoltUtil_WC_Gateway::matches_order($order,$payment,'ERC20','live','wc_123456'),'Historic USDT order rejects USDC');
check(BoltUtil_WC_Gateway::matches_order($order,$payment,'ERC20','live','wc_123456','USDC'),'New USDC order matches requested token');
$order->meta['_boltutil_token']='USDT';
check(!BoltUtil_WC_Gateway::matches_order($order,$payment,'ERC20','live','wc_123456','USDC'),'Stored token cannot be replaced by checkout retry');

$gateway->settings=array('tokens'=>array('USDC'),'networks'=>array('BEP20'));
check(array_keys($gateway->available_routes())===array('USDC:BEP20'),'BEP20 USDC route accepted only from server capabilities');
check('Binance-Peg USDC'===BoltUtil_WC_Gateway::token_label('USDC','BEP20'),'BEP20 USDC representation labeled explicitly');
check('USDC'===BoltUtil_WC_Gateway::token_label('USDC','ERC20'),'Native USDC label preserved');
$_POST=array('boltutil_route'=>'USDT:BEP20');
check(null===$select->invoke($gateway),'USDC-only configuration rejects BEP20 USDT');

$GLOBALS['capabilities']=array('routes'=>array(array('token'=>'USDC','network'=>'BASE'),array('token'=>'USDT','network'=>'BASE')));
$gateway->settings=array('tokens'=>array('USDT','USDC'),'networks'=>array('BASE'));
check(array_keys($gateway->available_routes())===array('USDC:BASE','USDT:BASE'),'Base routes require server capabilities');
check('Bridged USDT'===BoltUtil_WC_Gateway::token_label('USDT','BASE'),'Base USDT bridge representation is explicit');
check('USDC'===BoltUtil_WC_Gateway::token_label('USDC','BASE'),'Base USDC stays native');
$_POST=array('boltutil_route'=>'USDC:BASE');check('BASE'===$select->invoke($gateway)['network'],'Base route accepted');
$GLOBALS['capabilities']=array('routes'=>array());check(array() === $gateway->available_routes(),'Inactive Base is not offered');
