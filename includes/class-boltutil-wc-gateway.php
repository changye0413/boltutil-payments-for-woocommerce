<?php
defined( 'ABSPATH' ) || exit;

/** WooCommerce adapter; BoltUtil owns blockchain monitoring and matching. */
class BoltUtil_WC_Gateway extends WC_Payment_Gateway {
    private static $hooks_registered = false;

    const NETWORKS = array(
        'TRC20'   => 'TRON (TRC20)',
        'ERC20'   => 'Ethereum (ERC20)',
        'BEP20'   => 'BNB Smart Chain (BEP20)',
        'POLYGON' => 'Polygon',
        'SOLANA'  => 'Solana',
    );

    public function __construct() {
        $this->id                 = 'boltutil_usdt';
        $this->method_title       = 'BoltUtil USDT';
        $this->method_description = $this->guide_text( '通过 BoltUtil 收银台接收 USDT。', __( 'USDT payments through BoltUtil hosted checkout.', 'boltutil-payments-for-woocommerce' ) );
        $this->icon               = plugins_url( 'assets/boltutil-official-mark.svg', BOLTUTIL_WC_FILE );
        $this->has_fields         = true;
        $this->supports           = array( 'products' );
        $this->init_form_fields();
        $this->init_settings();
        $saved_title       = $this->get_option( 'title', 'USDT via BoltUtil' );
        $this->title       = 'USDT via BoltUtil' === $saved_title
            ? $this->guide_text( '使用 BoltUtil 支付 USDT', __( 'Pay USDT with BoltUtil', 'boltutil-payments-for-woocommerce' ) ) : $saved_title;
        $this->description = $this->get_option( 'description', '' );
        if ( ! self::$hooks_registered ) {
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_order_actions', array( $this, 'add_order_action' ) );
            add_action( 'woocommerce_order_action_boltutil_sync', array( $this, 'manual_sync' ) );
            add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'show_order_details' ) );
            self::$hooks_registered = true;
        }
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'setup_guide' => array( 'type' => 'boltutil_setup_guide' ),
            'settings_heading' => array( 'type' => 'boltutil_settings_heading' ),
            'enabled' => array( 'title' => $this->guide_text( '启用', __( 'Enable', 'boltutil-payments-for-woocommerce' ) ), 'type' => 'checkbox', 'label' => $this->guide_text( '启用 BoltUtil USDT', __( 'Enable BoltUtil USDT', 'boltutil-payments-for-woocommerce' ) ), 'default' => 'no' ),
            'title' => array( 'title' => $this->guide_text( '结账显示名称', __( 'Checkout title', 'boltutil-payments-for-woocommerce' ) ), 'type' => 'text', 'default' => 'USDT via BoltUtil', 'description' => $this->guide_text( '此名称由商户自定义，将显示在结账页。', __( 'Merchant-defined label shown at checkout.', 'boltutil-payments-for-woocommerce' ) ) ),
            'description' => array( 'title' => $this->guide_text( '结账说明', __( 'Checkout description', 'boltutil-payments-for-woocommerce' ) ), 'type' => 'textarea', 'default' => $this->guide_text( '选择网络后，前往 BoltUtil 收银台支付 USDT。', __( 'Pay USDT on the network you choose.', 'boltutil-payments-for-woocommerce' ) ) ),
            'live_api_key' => $this->secret_field( $this->guide_text( 'BoltUtil API Key（bt_live_）', __( 'BoltUtil API key (bt_live_)', 'boltutil-payments-for-woocommerce' ) ),
                $this->guide_text( '填写 BoltUtil 商户后台生成的 LIVE API Key。', __( 'Use the existing LIVE API key from your BoltUtil merchant dashboard.', 'boltutil-payments-for-woocommerce' ) ) ),
            'live_webhook_secret' => $this->secret_field( $this->guide_text( 'BoltUtil Webhook 密钥（whsec_）', __( 'BoltUtil Webhook secret (whsec_)', 'boltutil-payments-for-woocommerce' ) ),
                $this->guide_text( '填写同一商户已启用 Webhook 的密钥。它用于签名 API 请求及验证支付回调。', __( 'Use the secret for this merchant\'s active Webhook. It signs API requests and verifies payment callbacks.', 'boltutil-payments-for-woocommerce' ) ) ),
            'networks' => array( 'title' => $this->guide_text( '允许的 USDT 网络', __( 'Allowed USDT networks', 'boltutil-payments-for-woocommerce' ) ), 'type' => 'multiselect', 'class' => 'wc-enhanced-select', 'options' => self::NETWORKS, 'default' => array_keys( self::NETWORKS ), 'description' => $this->guide_text( '结账时还会检查 BoltUtil 中是否有已启用的钱包。', __( 'Live checkout also checks active BoltUtil settlement wallets.', 'boltutil-payments-for-woocommerce' ) ) ),
            'usage_guide' => array( 'type' => 'boltutil_usage_guide' ),
        );
    }

    private function guide_text( $chinese, $english ) {
        return boltutil_wc_text( $chinese, $english );
    }

    public function checkout_title() {
        return $this->title;
    }

    public function checkout_description() {
        $description = $this->description;
        if ( 'Pay USDT on the network you choose.' === $description || '选择网络后，前往 BoltUtil 收银台支付 USDT。' === $description ) {
            return $this->guide_text( '选择网络后，前往 BoltUtil 收银台支付 USDT。', __( 'Choose a network, then pay USDT at BoltUtil Checkout.', 'boltutil-payments-for-woocommerce' ) );
        }
        return $description;
    }

    public static function network_labels( $code ) {
        $labels = array(
            'TRC20' => array( 'TRON (TRC20)', 'TRC20' ),
            'ERC20' => array( 'Ethereum (ERC20)', 'ERC20' ),
            'BEP20' => array( 'BNB Smart Chain (BEP20)', 'BEP20' ),
            'POLYGON' => array( 'Polygon PoS', 'POLYGON' ),
            'SOLANA' => array( 'Solana', 'SOLANA' ),
        );
        return isset( $labels[ $code ] ) ? $labels[ $code ] : array( $code, $code );
    }

    public static function network_icon_url( $code ) {
        $icons = array(
            'TRC20' => 'tron.svg',
            'ERC20' => 'ethereum.svg',
            'BEP20' => 'bnb-chain.svg',
            'POLYGON' => 'polygon.svg',
            'SOLANA' => 'solana.svg',
        );
        return isset( $icons[ $code ] )
            ? plugins_url( 'assets/chains/' . $icons[ $code ], BOLTUTIL_WC_FILE ) : '';
    }

    public static function token_icon_url() {
        return plugins_url( 'assets/tokens/usdt.svg', BOLTUTIL_WC_FILE );
    }

    private function guide_step( $number, $title, $description, $url = '', $link_label = '' ) {
        $html = '<li class="boltutil-guide-step"><span class="boltutil-guide-number" aria-hidden="true">' . esc_html( $number ) . '</span>' .
            '<div><strong>' . esc_html( $title ) . '</strong><p>' . esc_html( $description ) . '</p>';
        if ( $url ) {
            $html .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $link_label ) . '<span aria-hidden="true"> ↗</span></a>';
        }
        return $html . '</div></li>';
    }

    private function guide_status( $label, $ready ) {
        return '<span class="boltutil-guide-status ' . ( $ready ? 'is-ready' : 'is-pending' ) . '">' .
            '<span aria-hidden="true">' . ( $ready ? '●' : '○' ) . '</span> ' . esc_html( $label ) . '</span>';
    }

    public function generate_boltutil_setup_guide_html( $key, $data ) {
        $api_key_saved = '' !== BoltUtil_WC_API::unseal( $this->get_option( 'live_api_key', '' ) );
        $webhook_saved = '' !== BoltUtil_WC_API::unseal( $this->get_option( 'live_webhook_secret', '' ) );
        $is_usd = 'USD' === get_woocommerce_currency();
        $enabled = 'yes' === $this->get_option( 'enabled', 'no' );
        $webhook_url = rest_url( 'boltutil/v1/webhook' );
        $html = '<tr class="boltutil-guide-row"><td colspan="2"><div class="boltutil-guide">';
        $html .= '<div class="boltutil-guide-hero"><img class="boltutil-guide-mark" src="' . esc_url( plugins_url( 'assets/boltutil-official-mark.svg', BOLTUTIL_WC_FILE ) ) . '" alt="" aria-hidden="true" />' .
            '<div><span class="boltutil-guide-eyebrow">BOLTUTIL · WOOCOMMERCE</span>' .
            '<h2>' . esc_html( $this->guide_text( '用 BoltUtil 接收 USDT', __( 'Accept USDT with BoltUtil', 'boltutil-payments-for-woocommerce' ) ) ) . '</h2>' .
            '<p>' . esc_html( $this->guide_text(
                '顾客在店铺下单并选择支付网络后，会跳转到 BoltUtil 收银台。链上付款确认后，BoltUtil 通知 WooCommerce 更新订单。资金直接进入你在 BoltUtil 配置的收款钱包。',
                __( 'Customers place an order in your store and pay on the selected network at BoltUtil Hosted Checkout. Once confirmed on-chain, BoltUtil updates the WooCommerce order. Funds go directly to your configured wallet.', 'boltutil-payments-for-woocommerce' )
            ) ) . '</p></div>';
        $html .= '<details class="boltutil-resource-menu"><summary aria-label="' .
            esc_attr( $this->guide_text( 'BoltUtil 更多信息', __( 'More BoltUtil information', 'boltutil-payments-for-woocommerce' ) ) ) .
            '"><span aria-hidden="true">&#8942;</span></summary><nav aria-label="' .
            esc_attr( $this->guide_text( 'BoltUtil 更多信息', __( 'More BoltUtil information', 'boltutil-payments-for-woocommerce' ) ) ) . '">';
        foreach ( boltutil_wc_resource_links() as $resource ) {
            $html .= '<a href="' . esc_url( $resource[0] ) . '"' .
                ( 0 === strpos( $resource[0], 'https://' ) ? ' target="_blank" rel="noopener noreferrer"' : '' ) .
                '>' . esc_html( $resource[1] ) . '</a>';
        }
        $html .= '</nav></details></div>';
        $html .= '<div class="boltutil-guide-body"><div class="boltutil-guide-heading"><h3>' .
            esc_html( $this->guide_text( '开始之前', __( 'Before you begin', 'boltutil-payments-for-woocommerce' ) ) ) . '</h3><p>' .
            esc_html( $this->guide_text( '按顺序完成以下步骤。无需在插件里填写钱包私钥或助记词。', __( 'Complete these steps in order. Never enter a wallet private key or recovery phrase in this plugin.', 'boltutil-payments-for-woocommerce' ) ) ) .
            '</p></div><ol class="boltutil-guide-steps">';
        $html .= $this->guide_step( '1', $this->guide_text( '创建 LIVE API Key', __( 'Create a LIVE API key', 'boltutil-payments-for-woocommerce' ) ),
            $this->guide_text( '登录 BoltUtil，在「API 密钥」页面生成 bt_live_ 开头的密钥。它用于识别这家商户。', __( 'Sign in to BoltUtil and create a key beginning with bt_live_ on the API Keys page. It identifies your merchant account.', 'boltutil-payments-for-woocommerce' ) ),
            'https://boltutil.com/dashboard/api-keys', $this->guide_text( '打开 API 密钥', __( 'Open API Keys', 'boltutil-payments-for-woocommerce' ) ) );
        $html .= $this->guide_step( '2', $this->guide_text( '配置收款钱包', __( 'Set up receiving wallets', 'boltutil-payments-for-woocommerce' ) ),
            $this->guide_text( '在「钱包」中为要接收的网络添加并启用 USDT 收款地址。结账时只显示插件允许且 BoltUtil 已启用钱包的网络。', __( 'Add and activate a USDT receiving address for each network you accept. Checkout shows networks allowed here that also have active BoltUtil wallets.', 'boltutil-payments-for-woocommerce' ) ),
            'https://boltutil.com/dashboard/wallets', $this->guide_text( '打开钱包', __( 'Open Wallets', 'boltutil-payments-for-woocommerce' ) ) );
        $html .= $this->guide_step( '3', $this->guide_text( '设置 Webhook', __( 'Set up the Webhook', 'boltutil-payments-for-woocommerce' ) ),
            $this->guide_text( '在 BoltUtil「Webhooks」中保存下方地址，并保存该配置的 whsec_ 回调密钥。地址必须属于当前店铺。', __( 'Save the URL below on the BoltUtil Webhooks page, then retain that configuration’s whsec_ secret. The URL must belong to this store.', 'boltutil-payments-for-woocommerce' ) ),
            'https://boltutil.com/dashboard/webhooks', $this->guide_text( '打开 Webhooks', __( 'Open Webhooks', 'boltutil-payments-for-woocommerce' ) ) );
        $html .= $this->guide_step( '4', $this->guide_text( '填写并启用插件', __( 'Save and enable this gateway', 'boltutil-payments-for-woocommerce' ) ),
            $this->guide_text( '在下方填入 API Key 和同一商户的 Webhook 密钥，选择网络，勾选启用并保存。商店货币须为 USD。', __( 'Enter the API key and Webhook secret for the same merchant below, select networks, enable the gateway, and save. Your store currency must be USD.', 'boltutil-payments-for-woocommerce' ) ) );
        $html .= '</ol><div class="boltutil-guide-webhook"><div><span class="boltutil-guide-field-label">' .
            esc_html( $this->guide_text( '本店 Webhook 地址', __( 'This store’s Webhook URL', 'boltutil-payments-for-woocommerce' ) ) ) . '</span><code id="boltutil-webhook-url">' . esc_html( $webhook_url ) . '</code></div>' .
            '<button type="button" class="button boltutil-copy-webhook" data-copied-label="' . esc_attr( $this->guide_text( '已复制', __( 'Copied', 'boltutil-payments-for-woocommerce' ) ) ) . '">' .
            esc_html( $this->guide_text( '复制地址', __( 'Copy URL', 'boltutil-payments-for-woocommerce' ) ) ) . '</button></div>';
        $html .= '<div class="boltutil-guide-statuses">' .
            $this->guide_status( $is_usd ? $this->guide_text( '商店货币：USD', __( 'Store currency: USD', 'boltutil-payments-for-woocommerce' ) ) : $this->guide_text( '请将商店货币设为 USD', __( 'Set store currency to USD', 'boltutil-payments-for-woocommerce' ) ), $is_usd ) .
            $this->guide_status( $api_key_saved ? $this->guide_text( 'API Key 已保存', __( 'API key saved', 'boltutil-payments-for-woocommerce' ) ) : $this->guide_text( 'API Key 未保存', __( 'API key missing', 'boltutil-payments-for-woocommerce' ) ), $api_key_saved ) .
            $this->guide_status( $webhook_saved ? $this->guide_text( 'Webhook 密钥已保存', __( 'Webhook secret saved', 'boltutil-payments-for-woocommerce' ) ) : $this->guide_text( 'Webhook 密钥未保存', __( 'Webhook secret missing', 'boltutil-payments-for-woocommerce' ) ), $webhook_saved ) .
            $this->guide_status( $enabled ? $this->guide_text( '支付方式已启用', __( 'Gateway enabled', 'boltutil-payments-for-woocommerce' ) ) : $this->guide_text( '支付方式未启用', __( 'Gateway disabled', 'boltutil-payments-for-woocommerce' ) ), $enabled ) . '</div>' .
            '<div class="boltutil-guide-quota"><strong>' . esc_html( $this->guide_text( '免费套餐：每日最多创建 30 个订单', __( 'Free plan: up to 30 orders created per day', 'boltutil-payments-for-woocommerce' ) ) ) . '</strong><p>' .
            esc_html( $this->guide_text( '这计算的是 BoltUtil 当日创建的订单数，不是成功付款数。达到上限后，新支付订单会被拒绝；请在 BoltUtil 查看套餐与升级选项。',
                __( 'This counts BoltUtil orders created today, not completed payments. New payment orders are rejected after the limit is reached. Review plans and upgrade options in BoltUtil.', 'boltutil-payments-for-woocommerce' ) ) ) .
            ' <a href="' . esc_url( 'https://boltutil.com/pricing' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $this->guide_text( '查看套餐 ↗', __( 'View plans ↗', 'boltutil-payments-for-woocommerce' ) ) ) . '</a></p></div>' .
            '<p class="boltutil-guide-small">' . esc_html( $this->guide_text( '状态只检查本店配置。钱包是否可用会在结账时由 BoltUtil API 核对。', __( 'These badges check local settings only. BoltUtil checks wallet availability during checkout.', 'boltutil-payments-for-woocommerce' ) ) ) . '</p>' .
            '</div></div></td></tr>';
        return $html;
    }

    public function generate_boltutil_usage_guide_html( $key, $data ) {
        $html = '<tr class="boltutil-guide-row"><td colspan="2"><div class="boltutil-guide boltutil-usage-guide"><div class="boltutil-guide-body">' .
            '<div class="boltutil-guide-heading"><h3>' . esc_html( $this->guide_text( '启用后如何使用', __( 'How it works after setup', 'boltutil-payments-for-woocommerce' ) ) ) . '</h3></div><ol class="boltutil-guide-usage">';
        $items = array(
            array( '01', $this->guide_text( '顾客下单', __( 'Customer places an order', 'boltutil-payments-for-woocommerce' ) ), $this->guide_text( '顾客在 USD 订单中选择 BoltUtil USDT 及网络，随后跳转到 BoltUtil 收银台。', __( 'For a USD order, the customer chooses BoltUtil USDT and a network, then goes to BoltUtil Checkout.', 'boltutil-payments-for-woocommerce' ) ) ),
            array( '02', $this->guide_text( '按收银台金额付款', __( 'Pay the checkout amount', 'boltutil-payments-for-woocommerce' ) ), $this->guide_text( '收银台显示该笔订单的准确 USDT 金额、收款地址、二维码和到期时间。不要用 WooCommerce 订单金额代替收银台实际应付金额。', __( 'Hosted Checkout shows the exact USDT amount, receiving address, QR code, and expiry. The checkout amount is authoritative.', 'boltutil-payments-for-woocommerce' ) ) ),
            array( '03', $this->guide_text( '确认后自动更新', __( 'Automatic update after confirmation', 'boltutil-payments-for-woocommerce' ) ), $this->guide_text( 'BoltUtil 确认链上付款后发送签名 Webhook；插件验证后更新 WooCommerce 订单。返回商店并不代表已经付款。', __( 'After on-chain confirmation, BoltUtil sends a signed Webhook and the plugin updates the WooCommerce order. Returning to the store does not mark it paid.', 'boltutil-payments-for-woocommerce' ) ) ),
            array( '04', $this->guide_text( '需要时手动同步', __( 'Recheck a pending order', 'boltutil-payments-for-woocommerce' ) ), $this->guide_text( '若订单长时间处于待付款状态，可在 WooCommerce 订单详情的「订单操作」中选择「同步 BoltUtil 支付状态」。插件也会定时自动查询。', __( 'If an order remains on hold, choose Recheck BoltUtil payment under Order actions in WooCommerce. Scheduled checks also run automatically.', 'boltutil-payments-for-woocommerce' ) ) ),
        );
        foreach ( $items as $item ) {
            $html .= '<li><span>' . esc_html( $item[0] ) . '</span><div><strong>' . esc_html( $item[1] ) . '</strong><p>' . esc_html( $item[2] ) . '</p></div></li>';
        }
        $html .= '</ol><div class="boltutil-guide-note"><strong>' . esc_html( $this->guide_text( '当前支持范围', __( 'Current scope', 'boltutil-payments-for-woocommerce' ) ) ) . '</strong><p>' .
            esc_html( $this->guide_text( '仅支持 USD 店铺使用 USDT，支持 TRC20、ERC20、BEP20、Polygon、Solana。USD 数字金额作为 USDT 计价基础；本版本不提供实时汇率锁定。链上转账是真实付款，请先用不转账的订单检查配置和跳转。',
                __( 'USDT for USD stores only, on TRC20, ERC20, BEP20, Polygon, and Solana. The USD numeric total is the USDT invoice basis; this version does not lock a live exchange rate. On-chain transfers are real payments, so check order creation and redirect without sending funds first.', 'boltutil-payments-for-woocommerce' ) ) ) .
            '</p></div><p class="boltutil-guide-help"><a href="' . esc_url( 'https://boltutil.com/developer-docs' ) . '" target="_blank" rel="noopener noreferrer">' .
            esc_html( $this->guide_text( '查看 BoltUtil API 文档 ↗', __( 'Read BoltUtil API docs ↗', 'boltutil-payments-for-woocommerce' ) ) ) . '</a></p></div></div></td></tr>';
        return $html;
    }

    public function generate_boltutil_settings_heading_html( $key, $data ) {
        return '<tr class="boltutil-settings-heading"><td colspan="2"><h3>' .
            esc_html( $this->guide_text( '支付方式设置', __( 'Payment settings', 'boltutil-payments-for-woocommerce' ) ) ) . '</h3><p>' .
            esc_html( $this->guide_text( '填写商户凭据、选择可用网络，然后保存更改。已保存的密钥仅显示脱敏摘要。',
                __( 'Enter your merchant credentials, choose supported networks, then save. Saved secrets appear only as masked hints.', 'boltutil-payments-for-woocommerce' ) ) ) .
            '</p></td></tr>';
    }

    private function secret_field( $title, $description ) {
        return array( 'title' => $title, 'type' => 'boltutil_secret', 'description' => $description );
    }

    public function generate_boltutil_secret_html( $key, $data ) {
        $saved = BoltUtil_WC_API::unseal( $this->get_option( $key, '' ) );
        $configured = '' !== $saved;
        $prefix = 'live_api_key' === $key ? 'bt_live_' : 'whsec_';
        $masked = $configured ? $prefix . '••••••••' . ( strlen( $saved ) >= 12 ? substr( $saved, -4 ) : '' ) : '';
        $label = $configured ? $this->guide_text( '已保存（脱敏显示）', __( 'Saved (masked)', 'boltutil-payments-for-woocommerce' ) ) : $this->guide_text( '尚未保存', __( 'Not configured', 'boltutil-payments-for-woocommerce' ) );
        $storage_note = $this->guide_text( '保存后输入框会清空。留空再次保存会保留现有密钥；输入新值才会替换。',
            __( 'This field clears after saving. Leave it blank to keep the saved secret, or enter a new value to replace it.', 'boltutil-payments-for-woocommerce' ) );
        return '<tr><th scope="row"><label for="' . esc_attr( $this->get_field_key( $key ) ) . '">' . esc_html( $data['title'] ) . '</label></th><td>' .
            '<input class="boltutil-secret-input" type="password" autocomplete="new-password" id="' . esc_attr( $this->get_field_key( $key ) ) . '" name="' . esc_attr( $this->get_field_key( $key ) ) . '" value="" placeholder="' . esc_attr( $masked ) . '" />' .
            '<span class="boltutil-secret-state' . ( $configured ? '' : ' is-pending' ) . '">' . esc_html( $label ) . '</span>' .
            ( $configured ? '<code class="boltutil-secret-preview">' . esc_html( $masked ) . '</code>' : '' ) .
            '<p class="description">' . esc_html( $data['description'] ) . '</p>' .
            '<p class="description">' . esc_html( $storage_note ) . '</p></td></tr>';
    }

    public function validate_boltutil_secret_field( $key, $value ) {
        // WooCommerce checks the payment settings form nonce before invoking field validators.
        $field_key = $this->get_field_key( $key );
        $raw = isset( $_POST[ $field_key ] ) && is_string( $_POST[ $field_key ] )
            ? trim( sanitize_text_field( wp_unslash( $_POST[ $field_key ] ) ) ) : '';
        return '' === $raw ? $this->get_option( $key, '' ) : BoltUtil_WC_API::seal( $raw );
    }

    public function is_available() {
        if ( ! parent::is_available() || 'USD' !== get_woocommerce_currency() ) {
            return false;
        }
        if ( '' === BoltUtil_WC_API::unseal( $this->get_option( 'live_webhook_secret', '' ) ) ) {
            return false;
        }
        return count( $this->available_networks() ) > 0;
    }

    public function available_networks() {
        $allowed = (array) $this->get_option( 'networks', array_keys( self::NETWORKS ) );
        $allowed = array_values( array_intersect( array_keys( self::NETWORKS ), $allowed ) );
        try {
            $settings = $this->settings;
            $api      = new BoltUtil_WC_API( $settings );
            $cache_key = 'boltutil_networks_' . md5( wp_json_encode( array( BoltUtil_WC_API::API_ORIGIN, $settings['live_api_key'] ?? '' ) ) );
            $active = get_transient( $cache_key );
            if ( false === $active ) {
                $active = $api->networks();
                if ( ! is_array( $active ) ) {
                    return array();
                }
                set_transient( $cache_key, $active, MINUTE_IN_SECONDS );
            }
            return array_values( array_intersect( $allowed, $active ) );
        } catch ( Throwable $error ) {
            return array();
        }
    }

    public function payment_fields() {
        $networks = $this->available_networks();
        echo '<div class="boltutil-payment-panel"><p class="boltutil-payment-description">' . esc_html( $this->checkout_description() ) . '</p>';
        echo '<fieldset class="boltutil-network-fieldset"><legend>' . esc_html( $this->guide_text( '选择 USDT 支付网络', __( 'Choose a USDT payment network', 'boltutil-payments-for-woocommerce' ) ) ) . '</legend><div class="boltutil-network-list">';
        foreach ( $networks as $code ) {
            $labels = self::network_labels( $code );
            echo '<label class="boltutil-network-option"><input type="radio" name="boltutil_network" value="' . esc_attr( $code ) . '" ' . checked( $code, $networks[0] ?? '', false ) . ' />';
            echo '<span class="boltutil-network-art" aria-hidden="true"><span class="boltutil-chain-badge network-' . esc_attr( strtolower( $code ) ) . '"><img src="' . esc_url( self::network_icon_url( $code ) ) . '" alt="" loading="lazy" /></span><span class="boltutil-token-badge"><img src="' . esc_url( self::token_icon_url() ) . '" alt="" loading="lazy" /></span></span>';
            echo '<span class="boltutil-network-copy"><strong>' . esc_html( $labels[0] ) . '</strong><small>' . esc_html( $labels[1] ) . '</small></span><span class="boltutil-network-arrow" aria-hidden="true">→</span>';
            echo '<span class="boltutil-network-selected" aria-hidden="true">✓ ' . esc_html( $this->guide_text( '已选择', __( 'Selected', 'boltutil-payments-for-woocommerce' ) ) ) . '</span></label>';
        }
        echo '</div></fieldset><p class="boltutil-payment-note">' . esc_html( $this->guide_text(
            '下单后将跳转到 BoltUtil 收银台。请按收银台显示的准确金额和地址付款；链上确认后，订单状态会自动更新。',
            __( 'You will be redirected to BoltUtil Checkout. Pay the exact amount to the address shown there; the order updates after on-chain confirmation.', 'boltutil-payments-for-woocommerce' )
        ) ) . '</p></div>';
    }

    public function validate_fields() {
        $network = isset( $_POST['boltutil_network'] ) ? sanitize_text_field( wp_unslash( $_POST['boltutil_network'] ) ) : '';
        if ( ! in_array( $network, $this->available_networks(), true ) ) {
            wc_add_notice( $this->guide_text( '请选择可用的 USDT 网络。', __( 'Choose an available USDT network.', 'boltutil-payments-for-woocommerce' ) ), 'error' );
            return false;
        }
        return true;
    }

    public function process_payment( $order_id ) {
        // A browser retry may repeat checkout. Bind all attempts for this Woo
        // order to one external ID and reuse the saved BoltUtil payment.
        $order = wc_get_order( $order_id );
        if ( ! $order || 'boltutil_usdt' !== $order->get_payment_method() || 'USD' !== $order->get_currency() ) {
            wc_add_notice( $this->guide_text( 'BoltUtil 目前仅支持 USD 订单。', __( 'BoltUtil currently supports USD orders only.', 'boltutil-payments-for-woocommerce' ) ), 'error' );
            return array( 'result' => 'failure' );
        }
        if ( $order->is_paid() ) {
            return array( 'result' => 'success', 'redirect' => $this->get_return_url( $order ) );
        }
        $network = isset( $_POST['boltutil_network'] ) ? sanitize_text_field( wp_unslash( $_POST['boltutil_network'] ) ) : '';
        if ( ! in_array( $network, $this->available_networks(), true ) ) {
            wc_add_notice( $this->guide_text( '请选择可用的 USDT 网络。', __( 'Choose an available USDT network.', 'boltutil-payments-for-woocommerce' ) ), 'error' );
            return array( 'result' => 'failure' );
        }

        $mode = 'live';
        $external_id = self::external_id( $order );
        $existing_id = $order->get_meta( '_boltutil_payment_id', true );
        try {
            $api = new BoltUtil_WC_API( $this->settings );
            if ( $existing_id ) {
                $payment = $api->get_payment( $existing_id );
            } else {
                $amount = (string) $order->get_total( 'edit' );
                if ( ! preg_match( '/^[0-9]{1,13}(\.[0-9]{1,6})?$/', $amount ) || (int) $amount < 1 ) {
                    throw new RuntimeException( 'The USD order amount is outside BoltUtil limits.' );
                }
                $payload = array(
                    'amount' => $amount,
                    'currency' => 'USD',
                    'token' => 'USDT',
                    'network' => $network,
                    'externalOrderId' => $external_id,
                    'successUrl' => $this->get_return_url( $order ),
                    'source' => 'woocommerce',
                    'merchantReference' => 'WooCommerce order ' . $order->get_id(),
                );
                try {
                    $payment = $api->create_payment( $payload, $external_id );
                } catch ( Throwable $first_error ) {
                    // A timed-out create can have committed. Read the same merchant-scoped ID.
                    $payment = $api->find_payment( $external_id );
                }
            }
            if ( ! self::matches_order( $order, $payment, $network, $mode, $external_id ) ) {
                throw new RuntimeException( 'BoltUtil returned a payment that cannot be used for this order.' );
            }
            if ( 'COMPLETED' === ( $payment['status'] ?? '' ) && $existing_id ) {
                self::reconcile_order( $order_id );
                $refreshed = wc_get_order( $order_id );
                if ( $refreshed && $refreshed->is_paid() ) {
                    return array( 'result' => 'success', 'redirect' => $this->get_return_url( $refreshed ) );
                }
            }
            if ( ! in_array( $payment['status'] ?? '', array( 'PENDING', 'CONFIRMING' ), true ) ||
                ! self::safe_checkout_url( $payment['checkoutUrl'] ?? '' ) ) {
                throw new RuntimeException( 'BoltUtil returned a payment that cannot be used for this order.' );
            }
            $order->update_meta_data( '_boltutil_payment_id', $payment['paymentId'] );
            $order->update_meta_data( '_boltutil_external_id', $external_id );
            $order->update_meta_data( '_boltutil_mode', $mode );
            $order->update_meta_data( '_boltutil_network', $network );
            $order->update_meta_data( '_boltutil_amount', (string) $payment['amount'] );
            $order->update_meta_data( '_boltutil_invoice_amount', (string) $payment['invoiceAmount'] );
            $order->update_meta_data( '_boltutil_checkout_url', $payment['checkoutUrl'] );
            $order->save();
            if ( ! $order->has_status( 'on-hold' ) ) {
                $order->update_status( 'on-hold', $this->guide_text( '等待 BoltUtil 确认 USDT 付款。', __( 'Awaiting confirmed USDT payment from BoltUtil.', 'boltutil-payments-for-woocommerce' ) ) );
            }
            if ( function_exists( 'WC' ) && WC()->cart ) {
                WC()->cart->empty_cart();
            }
            self::schedule_reconcile( $order_id, 300 );
            return array( 'result' => 'success', 'redirect' => $payment['checkoutUrl'] );
        } catch ( Throwable $error ) {
            if ( function_exists( 'wc_get_logger' ) ) {
                $diagnostic = $error instanceof RuntimeException ? $error->getMessage() : get_class( $error );
                wc_get_logger()->error(
                    'Payment creation failed for WooCommerce order ' . absint( $order_id ) . ': ' . sanitize_text_field( $diagnostic ),
                    array( 'source' => 'boltutil-woocommerce' )
                );
            }
            wc_add_notice( $this->guide_text( 'BoltUtil 支付订单创建失败，请重试。', __( 'BoltUtil payment could not be created. Please try again.', 'boltutil-payments-for-woocommerce' ) ), 'error' );
            return array( 'result' => 'failure' );
        }
    }

    public static function external_id( $order ) {
        // Include a site fingerprint so separate stores can share a merchant.
        return 'wc_' . substr( hash( 'sha256', home_url( '/' ) ), 0, 12 ) . '_' . $order->get_id();
    }

    public static function decimal_equal( $a, $b ) {
        // Compare normalized decimal strings: binary floats can change money.
        if ( ! is_scalar( $a ) || ! is_scalar( $b ) ||
            ! preg_match( '/^[0-9]{1,13}(\.[0-9]{1,6})?$/', (string) $a ) ||
            ! preg_match( '/^[0-9]{1,13}(\.[0-9]{1,6})?$/', (string) $b ) ) {
            return false;
        }
        $left  = explode( '.', (string) $a, 2 );
        $right = explode( '.', (string) $b, 2 );
        return ltrim( $left[0], '0' ) === ltrim( $right[0], '0' ) &&
            str_pad( $left[1] ?? '', 6, '0' ) === str_pad( $right[1] ?? '', 6, '0' );
    }

    public static function matches_order( $order, $payment, $network, $mode, $external_id ) {
        // A payment lookup must match the Woo order before redirect or update.
        return is_array( $payment ) &&
            preg_match( '/^[a-f0-9]{32}$/i', $payment['paymentId'] ?? '' ) &&
            $external_id === ( $payment['externalOrderId'] ?? '' ) &&
            $network === ( $payment['network'] ?? '' ) &&
            'USDT' === ( $payment['token'] ?? '' ) &&
            'USD' === ( $payment['invoiceCurrency'] ?? '' ) &&
            self::decimal_equal( $order->get_total( 'edit' ), $payment['invoiceAmount'] ?? '' ) &&
            ( ! $order->get_meta( '_boltutil_payment_id', true ) || $order->get_meta( '_boltutil_payment_id', true ) === $payment['paymentId'] ) &&
            ( ! $order->get_meta( '_boltutil_mode', true ) || $order->get_meta( '_boltutil_mode', true ) === $mode );
    }

    public static function safe_checkout_url( $url ) {
        return is_string( $url ) && 'https' === wp_parse_url( $url, PHP_URL_SCHEME ) && (bool) wp_http_validate_url( $url );
    }

    public function add_order_action( $actions ) {
        $actions['boltutil_sync'] = $this->guide_text( '同步 BoltUtil 支付状态', __( 'Recheck BoltUtil payment', 'boltutil-payments-for-woocommerce' ) );
        return $actions;
    }

    public function show_order_details( $order ) {
        if ( ! $order || 'boltutil_usdt' !== $order->get_payment_method() ) {
            return;
        }
        $fields = array(
            $this->guide_text( '支付 ID', __( 'Payment ID', 'boltutil-payments-for-woocommerce' ) ) => '_boltutil_payment_id',
            $this->guide_text( '模式', __( 'Mode', 'boltutil-payments-for-woocommerce' ) ) => '_boltutil_mode',
            $this->guide_text( '网络', __( 'Network', 'boltutil-payments-for-woocommerce' ) ) => '_boltutil_network',
            $this->guide_text( '账单金额（USD）', __( 'Invoice amount (USD)', 'boltutil-payments-for-woocommerce' ) ) => '_boltutil_invoice_amount',
            $this->guide_text( '应付金额（USDT）', __( 'Payable amount (USDT)', 'boltutil-payments-for-woocommerce' ) ) => '_boltutil_amount',
        );
        echo '<div class="boltutil-order-details"><h3>BoltUtil USDT</h3>';
        foreach ( $fields as $label => $key ) {
            $value = $order->get_meta( $key, true );
            if ( '' !== (string) $value ) {
                echo '<p><strong>' . esc_html( $label ) . ':</strong> ' . esc_html( (string) $value ) . '</p>';
            }
        }
        echo '</div>';
    }

    public function manual_sync( $order ) {
        if ( $order && 'boltutil_usdt' === $order->get_payment_method() ) {
            self::reconcile_order( $order->get_id() );
        }
    }

    public static function record_terminal_status( $order, $status ) {
        if ( ! in_array( $status, array( 'EXPIRED', 'FAILED', 'CANCELLED' ), true ) || $order->is_paid() ) {
            return false;
        }
        if ( $order->has_status( 'failed' ) ) {
            return true;
        }
        if ( ! $order->has_status( array( 'pending', 'on-hold' ) ) ) {
            return false;
        }
        /* translators: %s is the verified BoltUtil payment status. */
        $message = __( 'BoltUtil payment status: %s.', 'boltutil-payments-for-woocommerce' );
        $order->update_status( 'failed', sprintf( boltutil_wc_text( 'BoltUtil 支付状态：%s。', $message ), $status ) );
        return true;
    }

    public static function reconcile_order( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order || 'boltutil_usdt' !== $order->get_payment_method() || $order->is_paid() ) {
            return;
        }
        $payment_id = $order->get_meta( '_boltutil_payment_id', true );
        if ( ! $payment_id ) {
            return;
        }
        $gateway = new self();
        $mode = $order->get_meta( '_boltutil_mode', true );
        if ( 'live' !== $mode ) {
            return;
        }
        try {
            $settings = $gateway->settings;
            $payment = ( new BoltUtil_WC_API( $settings ) )->get_payment( $payment_id );
            if ( ! self::matches_order( $order, $payment, $order->get_meta( '_boltutil_network', true ),
                $order->get_meta( '_boltutil_mode', true ), self::external_id( $order ) ) ||
                ! self::decimal_equal( $order->get_meta( '_boltutil_amount', true ), $payment['amount'] ?? '' ) ) {
                return;
            }
            if ( 'COMPLETED' === ( $payment['status'] ?? '' ) ) {
                $txid = isset( $payment['txHash'] ) && is_string( $payment['txHash'] ) ? sanitize_text_field( $payment['txHash'] ) : '';
                $order->payment_complete( $txid );
                $order->add_order_note( $gateway->guide_text( 'BoltUtil 已确认这笔 USDT 付款。', __( 'BoltUtil confirmed this USDT payment.', 'boltutil-payments-for-woocommerce' ) ) );
                $order->save();
            } elseif ( 'PENDING' === ( $payment['status'] ?? '' ) || 'CONFIRMING' === ( $payment['status'] ?? '' ) ) {
                self::schedule_reconcile( $order_id, 300 );
            } else {
                self::record_terminal_status( $order, $payment['status'] ?? '' );
            }
        } catch ( Throwable $error ) {
            self::schedule_reconcile( $order_id, 600 );
        }
    }

    private static function schedule_reconcile( $order_id, $delay ) {
        $args = array( (int) $order_id );
        if ( function_exists( 'as_schedule_single_action' ) &&
            function_exists( 'as_next_scheduled_action' ) &&
            ! as_next_scheduled_action( 'boltutil_wc_reconcile_order', $args, 'boltutil' ) ) {
            as_schedule_single_action( time() + $delay, 'boltutil_wc_reconcile_order', $args, 'boltutil' );
        }
    }
}
