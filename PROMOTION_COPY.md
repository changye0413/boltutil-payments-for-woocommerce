# Release copy — v0.5.2

## Repository description (English)

USDT and USDC payments for WooCommerce through BoltUtil Hosted Checkout, including Base, signed Webhooks and merchant-scoped status checks.

## Short announcement (English)

BoltUtil Payments for WooCommerce is open source. Version 0.5.2 adds USDC and Base alongside existing USDT networks. Buyers choose an enabled asset/network route in WooCommerce and pay on BoltUtil Hosted Checkout. The plugin verifies signed events and payment status before completing orders. Download the early release from GitHub; real multi-asset payment and production Webhook acceptance remain pending.

## 简短介绍（中文）

BoltUtil 的 WooCommerce 支付插件已开源。0.5.2 增加 USDC 与 Base 支持，保留原有 USDT 网络；顾客选择已启用的币种和网络后，进入 BoltUtil 收银台付款。插件校验签名回调并复查支付状态，再更新 WooCommerce 订单。当前提供早期版本下载，真实多币种付款与生产回调闭环仍待验收。

## Download and setup

Download `boltutil-payments-for-woocommerce-0.5.2.zip` from the [release page](https://github.com/changye0413/boltutil-payments-for-woocommerce/releases/tag/v0.5.2), upload it under **WordPress → Plugins → Add New → Upload Plugin**, and follow the [setup guide](README.md#configure-in-five-steps).

Requirements: WordPress 6.5+, PHP 7.4+, WooCommerce 11.1+, HTTPS, a USD store, a LIVE BoltUtil API key, matching active Webhook and receiving wallets. Routes are offered only when allowed by the plugin and enabled by the BoltUtil service. Base USDT is Bridged USDT; BEP20 USDC is Binance-Peg USDC.

The free plan permits up to 30 newly created BoltUtil orders per server day. Read the service pricing, privacy policy and terms before configuring the gateway. Pay the exact token amount shown at checkout; network fees are separate.

Do not announce WordPress.org availability until the real-payment acceptance and directory approval are complete. Full GitHub release notes are maintained in [.github/RELEASE_NOTES.md](.github/RELEASE_NOTES.md).
