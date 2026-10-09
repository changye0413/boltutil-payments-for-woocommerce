<?php
defined( 'ABSPATH' ) || exit;

final class BoltUtil_WC_Blocks extends \Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
    protected $name = 'boltutil_usdt';
    private $gateway;

    public function initialize() {
        $this->gateway = new BoltUtil_WC_Gateway();
    }

    public function is_active() {
        return $this->gateway && $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {
        wp_register_script( 'boltutil-wc-routes', plugins_url( 'assets/routes.js', BOLTUTIL_WC_FILE ), array(), BOLTUTIL_WC_VERSION, true );
        wp_register_script(
            'boltutil-wc-blocks',
            plugins_url( 'assets/blocks.js', BOLTUTIL_WC_FILE ),
            array( 'wp-element', 'wp-html-entities', 'wc-blocks-registry', 'wc-settings', 'boltutil-wc-routes' ),
            BOLTUTIL_WC_VERSION,
            true
        );
        return array( 'boltutil-wc-blocks' );
    }

    public function get_payment_method_data() {
        $routes = $this->gateway->available_routes();
        $networks = array();
        foreach ( $routes as $route_id => $route ) {
            $code = $route['network'];
            $labels = BoltUtil_WC_Gateway::network_labels( $code );
            $networks[ $route_id ] = array(
                'label' => $labels[0],
                'short' => BoltUtil_WC_Gateway::token_label( $route['token'], $code ) . ' · ' . $labels[1],
                'token' => $route['token'],
                'network' => $code,
                'tokenIcon' => BoltUtil_WC_Gateway::token_icon_url( $route['token'] ),
                'icon' => BoltUtil_WC_Gateway::network_icon_url( $code ),
            );
        }
        return array(
            'title'       => $this->gateway->checkout_title(),
            'description' => $this->gateway->checkout_description(),
            'networks'    => $networks,
            'icon'        => plugins_url( 'assets/boltutil-official-mark.svg', BOLTUTIL_WC_FILE ),
            'tokenIcon'   => BoltUtil_WC_Gateway::token_icon_url(),
            'tokenLabel' => boltutil_wc_text( '选择支付币种', __( 'Choose a stablecoin', 'boltutil-payments-for-woocommerce' ) ),
            'tokenIcons' => array( 'USDT' => BoltUtil_WC_Gateway::token_icon_url( 'USDT' ), 'USDC' => BoltUtil_WC_Gateway::token_icon_url( 'USDC' ) ),
            'networkLabel' => boltutil_wc_text( '选择支付网络', __( 'Choose a payment network', 'boltutil-payments-for-woocommerce' ) ),
            'selectedLabel' => boltutil_wc_text( '已选择', __( 'Selected', 'boltutil-payments-for-woocommerce' ) ),
            'redirectNotice' => boltutil_wc_text(
                '下单后将跳转到 BoltUtil 收银台。请按收银台显示的准确金额和地址付款；链上确认后，订单状态会自动更新。',
                __( 'You will be redirected to BoltUtil Checkout. Pay the exact amount to the address shown there; the order updates after on-chain confirmation.', 'boltutil-payments-for-woocommerce' )
            ),
            'networkError' => boltutil_wc_text( '请选择可用的币种和网络。', __( 'Choose an available stablecoin and network.', 'boltutil-payments-for-woocommerce' ) ),
            'supports'    => array( 'products' ),
        );
    }
}
