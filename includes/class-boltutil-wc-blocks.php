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
        wp_register_script(
            'boltutil-wc-blocks',
            plugins_url( 'assets/blocks.js', BOLTUTIL_WC_FILE ),
            array( 'wp-element', 'wp-html-entities', 'wc-blocks-registry', 'wc-settings' ),
            BOLTUTIL_WC_VERSION,
            true
        );
        return array( 'boltutil-wc-blocks' );
    }

    public function get_payment_method_data() {
        $codes = $this->gateway->available_networks();
        $networks = array();
        foreach ( $codes as $code ) {
            $labels = BoltUtil_WC_Gateway::network_labels( $code );
            $networks[ $code ] = array(
                'label' => $labels[0],
                'short' => $labels[1],
                'icon' => BoltUtil_WC_Gateway::network_icon_url( $code ),
            );
        }
        return array(
            'title'       => $this->gateway->checkout_title(),
            'description' => $this->gateway->checkout_description(),
            'networks'    => $networks,
            'icon'        => plugins_url( 'assets/boltutil-official-mark.svg', BOLTUTIL_WC_FILE ),
            'tokenIcon'   => BoltUtil_WC_Gateway::token_icon_url(),
            'networkLabel' => boltutil_wc_text( '选择 USDT 支付网络', __( 'Choose a USDT payment network', 'boltutil-payments-for-woocommerce' ) ),
            'selectedLabel' => boltutil_wc_text( '已选择', __( 'Selected', 'boltutil-payments-for-woocommerce' ) ),
            'redirectNotice' => boltutil_wc_text(
                '下单后将跳转到 BoltUtil 收银台。请按收银台显示的准确金额和地址付款；链上确认后，订单状态会自动更新。',
                __( 'You will be redirected to BoltUtil Checkout. Pay the exact amount to the address shown there; the order updates after on-chain confirmation.', 'boltutil-payments-for-woocommerce' )
            ),
            'networkError' => boltutil_wc_text( '请选择可用的 USDT 网络。', __( 'Choose an available USDT network.', 'boltutil-payments-for-woocommerce' ) ),
            'supports'    => array( 'products' ),
        );
    }
}
