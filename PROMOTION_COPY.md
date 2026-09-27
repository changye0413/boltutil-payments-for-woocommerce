# Release copy

## Repository description (English)

USDT payments for WooCommerce through BoltUtil Hosted Checkout, with five networks, signed Webhooks, and merchant-scoped status checks.

## Short announcement (English)

BoltUtil Payments for WooCommerce is now open source. Offer USDT on TRON, Ethereum, BNB Smart Chain, Polygon PoS, and Solana in a USD store. Buyers choose a network in WooCommerce and pay on BoltUtil Hosted Checkout. The plugin verifies signed events and payment status before completing orders. Download the early release from GitHub; a real WooCommerce payment and production callback acceptance test is still pending.

## 简短介绍（中文）

BoltUtil 的 WooCommerce USDT 插件现已开源。USD 店铺可提供 TRON、Ethereum、BNB Smart Chain、Polygon PoS 和 Solana 五条网络；顾客选择网络后进入 BoltUtil 收银台。插件会校验签名回调并复查支付状态，再更新 WooCommerce 订单。当前提供早期版本下载，WooCommerce 发起的真实付款与生产回调闭环仍待验收。

## GitHub release notes for v0.4.18

**Early release — test with care.** This ZIP installs BoltUtil USDT as a WooCommerce payment method. It supports classic checkout and Checkout Blocks, five USDT networks, Hosted Checkout redirection, signed Webhook verification, manual and scheduled status reconciliation, and 11 bundled translations. The network and USDT icons are CC0 assets with an included license notice. API requests are pinned to the official BoltUtil API origin.

Requirements: WordPress 6.5+, PHP 7.4+, WooCommerce, HTTPS, a USD store, a LIVE BoltUtil API key, a matching active Webhook secret, and configured receiving wallets. Download `boltutil-payments-for-woocommerce-0.4.18.zip`, upload it under **Plugins → Add New → Upload Plugin**, and follow the [setup guide](README.md#configure-in-five-steps).

Order creation and Hosted Checkout redirect have been checked on a test store. A real WooCommerce-originated blockchain payment with a production completion Webhook has not yet been verified. Do not claim that end-to-end acceptance is complete.
