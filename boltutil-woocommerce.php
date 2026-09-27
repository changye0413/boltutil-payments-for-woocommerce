<?php
/**
 * Plugin Name: BoltUtil Payments for WooCommerce
 * Description: Accept USDT through BoltUtil hosted checkout on supported networks.
 * Version: 0.4.18
 * Requires at least: 6.5
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Author: BoltUtil
 * Author URI: https://boltutil.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: boltutil-payments-for-woocommerce
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'BOLTUTIL_WC_VERSION', '0.4.18' );
define( 'BOLTUTIL_WC_FILE', __FILE__ );
define( 'BOLTUTIL_WC_PATH', plugin_dir_path( __FILE__ ) );

add_action( 'admin_enqueue_scripts', 'boltutil_wc_enqueue_admin_assets' );
add_action( 'init', 'boltutil_wc_load_translations' );
add_filter( 'plugin_action_links_' . plugin_basename( BOLTUTIL_WC_FILE ), 'boltutil_wc_plugin_action_links' );
add_filter( 'plugin_row_meta', 'boltutil_wc_plugin_row_meta', 10, 2 );

function boltutil_wc_load_translations() {
    // Register the bundled catalog for manually installed ZIPs. WordPress.org
    // language packs retain priority when this plugin is published there.
    load_plugin_textdomain( 'boltutil-payments-for-woocommerce', false, dirname( plugin_basename( BOLTUTIL_WC_FILE ) ) . '/languages' );
}

function boltutil_wc_text( $chinese, $english ) {
    // Prefer WordPress language packs. Keep the original Simplified Chinese copy
    // as a fallback while this plugin is distributed outside WordPress.org.
    if ( 'zh_CN' === determine_locale() && ! is_textdomain_loaded( 'boltutil-payments-for-woocommerce' ) ) {
        return $chinese;
    }
    return $english;
}

function boltutil_wc_resource_links() {
    return array(
        array( 'https://boltutil.com/pricing', boltutil_wc_text( '查看定价和费用', __( 'View pricing and fees', 'boltutil-payments-for-woocommerce' ) ) ),
        array( 'https://boltutil.com/developer-docs', boltutil_wc_text( '了解更多', __( 'Learn more', 'boltutil-payments-for-woocommerce' ) ) ),
        array( 'https://boltutil.com/terms', boltutil_wc_text( '查看服务条款', __( 'View terms of service', 'boltutil-payments-for-woocommerce' ) ) ),
        array( 'mailto:support@boltutil.com', boltutil_wc_text( '联系支持', __( 'Contact support', 'boltutil-payments-for-woocommerce' ) ) ),
    );
}

function boltutil_wc_plugin_action_links( $actions ) {
    if ( current_user_can( 'manage_woocommerce' ) ) {
        $url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=boltutil_usdt' );
        $actions['boltutil_settings'] = '<a href="' . esc_url( $url ) . '">' .
            esc_html( boltutil_wc_text( '支付设置', __( 'Payment settings', 'boltutil-payments-for-woocommerce' ) ) ) . '</a>';
    }
    return $actions;
}

function boltutil_wc_plugin_row_meta( $meta, $plugin_file ) {
    if ( plugin_basename( BOLTUTIL_WC_FILE ) !== $plugin_file ) {
        return $meta;
    }
    foreach ( boltutil_wc_resource_links() as $resource ) {
        $meta[] = '<a href="' . esc_url( $resource[0] ) . '"' .
            ( 0 === strpos( $resource[0], 'https://' ) ? ' target="_blank" rel="noopener noreferrer"' : '' ) .
            '>' . esc_html( $resource[1] ) . '</a>';
    }
    return $meta;
}

function boltutil_wc_enqueue_admin_assets( $hook ) {
    if ( 'woocommerce_page_wc-settings' !== $hook ||
        ! isset( $_GET['tab'], $_GET['section'] ) ||
        'checkout' !== sanitize_key( wp_unslash( $_GET['tab'] ) ) ||
        'boltutil_usdt' !== sanitize_key( wp_unslash( $_GET['section'] ) ) ) {
        return;
    }
    wp_enqueue_style( 'boltutil-wc-admin', plugins_url( 'assets/admin.css', BOLTUTIL_WC_FILE ), array(), BOLTUTIL_WC_VERSION );
    wp_enqueue_script( 'boltutil-wc-admin', plugins_url( 'assets/admin.js', BOLTUTIL_WC_FILE ), array(), BOLTUTIL_WC_VERSION, true );
}

register_activation_hook( __FILE__, 'boltutil_wc_install' );

function boltutil_wc_install() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $table = $wpdb->prefix . 'boltutil_events';
    $sql   = "CREATE TABLE {$table} (
        event_id varchar(80) NOT NULL,
        order_id bigint(20) unsigned NOT NULL,
        payment_id varchar(80) NOT NULL,
        status varchar(16) NOT NULL,
        lease_until datetime DEFAULT NULL,
        processed_at datetime DEFAULT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (event_id),
        KEY order_payment (order_id,payment_id)
    ) {$wpdb->get_charset_collate()};";
    dbDelta( $sql );
}

add_action( 'before_woocommerce_init', function () {
    if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', BOLTUTIL_WC_FILE, true );
    }
} );

add_action( 'plugins_loaded', function () {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }

    // Upgrade from the sandbox prototype: discard its stored credentials.
    $settings = get_option( 'woocommerce_boltutil_usdt_settings', array() );
    if ( is_array( $settings ) &&
        ( isset( $settings['mode'] ) || isset( $settings['test_api_key'] ) || isset( $settings['test_signing_secret'] ) ) ) {
        unset( $settings['mode'], $settings['test_api_key'], $settings['test_signing_secret'] );
        $settings['enabled'] = 'no';
        unset( $settings['api_url'] );
        update_option( 'woocommerce_boltutil_usdt_settings', $settings );
    }

    require_once BOLTUTIL_WC_PATH . 'includes/class-boltutil-wc-api.php';
    if ( is_array( $settings ) && isset( $settings['live_signing_secret'] ) ) {
        $old_key = BoltUtil_WC_API::unseal( $settings['live_api_key'] ?? '' );
        unset( $settings['live_signing_secret'] );
        if ( 0 === strpos( $old_key, 'btint_' ) ) {
            $settings['enabled'] = 'no';
        }
        update_option( 'woocommerce_boltutil_usdt_settings', $settings );
    }
    require_once BOLTUTIL_WC_PATH . 'includes/class-boltutil-wc-gateway.php';
    require_once BOLTUTIL_WC_PATH . 'includes/class-boltutil-wc-webhook.php';

    add_filter( 'woocommerce_payment_gateways', function ( $gateways ) {
        $gateways[] = 'BoltUtil_WC_Gateway';
        return $gateways;
    } );

    add_action( 'rest_api_init', array( 'BoltUtil_WC_Webhook', 'register_route' ) );
    add_action( 'boltutil_wc_reconcile_order', array( 'BoltUtil_WC_Gateway', 'reconcile_order' ) );

    add_action( 'woocommerce_blocks_loaded', function () {
        if ( ! class_exists( '\\Automattic\\WooCommerce\\Blocks\\Payments\\Integrations\\AbstractPaymentMethodType' ) ) {
            return;
        }
        require_once BOLTUTIL_WC_PATH . 'includes/class-boltutil-wc-blocks.php';
        add_action( 'woocommerce_blocks_payment_method_type_registration', function ( $registry ) {
            $registry->register( new BoltUtil_WC_Blocks() );
        } );
    } );

    add_action( 'wp_enqueue_scripts', function () {
        if ( function_exists( 'is_checkout' ) && is_checkout() &&
            ( ! function_exists( 'is_order_received_page' ) || ! is_order_received_page() ) ) {
            wp_enqueue_style( 'boltutil-wc-checkout', plugins_url( 'assets/checkout.css', BOLTUTIL_WC_FILE ), array(), BOLTUTIL_WC_VERSION );
        }
    } );
} );
