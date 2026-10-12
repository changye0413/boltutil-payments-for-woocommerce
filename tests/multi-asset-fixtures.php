<?php
/** Exercise the actual gateway, including historic metadata defaults and server route filtering. */
define('ABSPATH', __DIR__);
define('MINUTE_IN_SECONDS', 60);
class WC_Payment_Gateway {
    public $settings = array();
    public function get_option($name, $default) { return $this->settings[$name] ?? $default; }
    public function get_return_url($order) { return 'https://store.example/thanks'; }
}
class BoltUtil_WC_API {
    public function __construct($settings) {}
    public function capabilities() { return $GLOBALS['capabilities']; }
    public function create_payment($payload,$key) {
        $GLOBALS['creates'][]=array($payload,$key);
        if (!empty($GLOBALS['create_timeout'])) throw new RuntimeException('Synthetic timeout');
        return $GLOBALS['payment'];
    }
    public function get_payment($id) { $GLOBALS['reads'][]=$id;return $GLOBALS['payment']; }
    public function find_payment($external) { $GLOBALS['finds'][]=$external;return $GLOBALS['payment']; }
}
function get_transient($key) { return false; }
function set_transient($key, $value, $seconds) {}
function wp_json_encode($value) { return json_encode($value); }
function sanitize_text_field($value) { return $value; }
function wp_unslash($value) { return $value; }
function home_url($path) { return 'https://store.example/'; }
function wp_parse_url($url,$part) { return parse_url($url,$part); }
function wp_http_validate_url($url) { return $url; }
function wc_get_order($id) { return $GLOBALS['checkout_order']; }
function wc_add_notice($message,$type) { $GLOBALS['notices'][]=$message; }
function boltutil_wc_text($chinese,$english) { return $english; }
function __($value,$domain) { return $value; }
require __DIR__ . '/../includes/class-boltutil-wc-gateway.php';
class Asset_Order {
    public $meta = array();
    public function get_meta($key, $single) { return $this->meta[$key] ?? ''; }
    public function get_total($context) { return '1.00'; }
}
class Checkout_Order extends Asset_Order {
    public $currency='USD',$status='pending',$paid=false,$total='1.00';
    public function get_id() { return 42; }
    public function get_currency() { return $this->currency; }
    public function get_total($context) { return $this->total; }
    public function get_payment_method() { return 'boltutil_usdt'; }
    public function is_paid() { return $this->paid; }
    public function update_meta_data($key,$value) { $this->meta[$key]=$value; }
    public function save() {}
    public function has_status($status) { return $this->status===$status; }
    public function update_status($status,$note) { $this->status=$status; }
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

$GLOBALS['capabilities']=array('routes'=>array());
foreach(array('USDT','USDC') as $token) foreach(array_keys(BoltUtil_WC_Gateway::NETWORKS) as $network) {
    $GLOBALS['capabilities']['routes'][]=array('token'=>$token,'network'=>$network);
}
$gateway->settings=array('tokens'=>array('USDT','USDC'),'networks'=>array_keys(BoltUtil_WC_Gateway::NETWORKS));
check(11===count($gateway->available_routes()),'Six chains and two tokens expose exactly eleven supported combinations');
check(!isset($gateway->available_routes()['USDC:TRC20']),'TRON USDC is excluded even if erroneously advertised by the service');
$_POST=array('boltutil_route'=>'USDT:BASE','boltutil_token'=>'USDC');
check(null===$select->invoke($gateway),'Mismatched submitted coin and route are rejected');
$_POST=array('boltutil_route'=>'USDC:BASE','boltutil_token'=>'USDC');
check('USDC'===$select->invoke($gateway)['token'],'Consistent coin and network selection accepted');

// Exercise the actual create/redirect path for every route. Network calls are intercepted.
$GLOBALS['creates']=array();$GLOBALS['reads']=array();$GLOBALS['finds']=array();
foreach ($gateway->available_routes() as $route) {
    $order=new Checkout_Order();$GLOBALS['checkout_order']=$order;
    $_POST=array('boltutil_route'=>$route['token'].':'.$route['network'],'boltutil_token'=>$route['token']);
    $external=BoltUtil_WC_Gateway::external_id($order);
    $GLOBALS['payment']=array('paymentId'=>str_repeat('a',32),'externalOrderId'=>$external,'network'=>$route['network'],
        'token'=>$route['token'],'invoiceCurrency'=>'USD','invoiceAmount'=>'1.00','amount'=>'1.001',
        'status'=>'PENDING','checkoutUrl'=>'https://boltutil.com/en/checkout/'.str_repeat('a',32));
    $result=$gateway->process_payment(42);
    check('success'===$result['result'] && 'on-hold'===$order->status && !$order->paid,'Create and redirect without marking paid: '.$route['token'].':'.$route['network']);
    check($route['token']===$order->meta['_boltutil_token'] && $route['network']===$order->meta['_boltutil_network'] && '1.001'===$order->meta['_boltutil_amount'],'Exact returned quote and selected route saved');
    list($payload,$key)=end($GLOBALS['creates']);
    check('1.00'===$payload['amount'] && $external===$payload['externalOrderId'] && $external===$key,'Invoice and idempotency key preserved');
    $before=count($GLOBALS['creates']);$gateway->process_payment(42);
    check($before===count($GLOBALS['creates']),'Retry uses saved payment instead of creating another');
}
$order=new Checkout_Order();$GLOBALS['checkout_order']=$order;$GLOBALS['create_timeout']=true;
$result=$gateway->process_payment(42);$GLOBALS['create_timeout']=false;
check('success'===$result['result'] && end($GLOBALS['finds'])===BoltUtil_WC_Gateway::external_id($order),'Timed-out create recovers only by the same external ID');
$order=new Checkout_Order();$order->currency='EUR';$GLOBALS['checkout_order']=$order;$before=count($GLOBALS['creates']);
check('failure'===$gateway->process_payment(42)['result'] && $before===count($GLOBALS['creates']),'Non-USD orders rejected before creating payment');
foreach(array('0.99','0.01','1e0','1.0000001') as $total){
    $order=new Checkout_Order();$order->total=$total;$GLOBALS['checkout_order']=$order;$before=count($GLOBALS['creates']);
    check('failure'===$gateway->process_payment(42)['result'] && $before===count($GLOBALS['creates']),'Out-of-range invoice rejected: '.$total);
}
$order=new Checkout_Order();$GLOBALS['checkout_order']=$order;$GLOBALS['payment']['token']='UNKNOWN';
check('failure'===$gateway->process_payment(42)['result'] && !$order->paid && empty($order->meta),'Mismatched service quote cannot redirect or bind an order');
$GLOBALS['payment']['token']='USDC';$GLOBALS['payment']['status']='COMPLETED';
check('failure'===$gateway->process_payment(42)['result'] && !$order->paid,'Unlinked completed payment cannot mark checkout paid');
check(BoltUtil_WC_Gateway::decimal_equal('1.000001','01.000001') && !BoltUtil_WC_Gateway::decimal_equal('1.000001','1.000002'),'Decimal comparison keeps smallest supported difference');
check(!BoltUtil_WC_Gateway::decimal_equal('1e0','1') && !BoltUtil_WC_Gateway::decimal_equal(array('1'),'1'),'Malformed monetary values rejected');
