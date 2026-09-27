=== BoltUtil Payments for WooCommerce ===
Tags: woocommerce, payments, usdt, crypto, stablecoin
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 0.4.18
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Accept USDT on five supported networks through BoltUtil Hosted Checkout. Funds go directly to the merchant's configured wallet.

== Description ==

BoltUtil connects WooCommerce to the BoltUtil payment service. Customers select a USDT network, place an order, and continue to BoltUtil Hosted Checkout. BoltUtil creates the payment, displays the receiving address and exact payable amount, monitors the network, and reports the confirmed result to WooCommerce. The plugin does not receive cryptocurrency or store wallet private keys or recovery phrases.

Current payment support is USDT on TRON (TRC20), Ethereum (ERC20), BNB Smart Chain (BEP20), Polygon PoS, and Solana. A merchant must enable the corresponding receiving wallet in BoltUtil. USDC, Base, and other assets or networks are planned, but are not supported by this release.

Only WooCommerce stores using USD are supported. The USD numeric order total is the USDT invoice basis; this release does not provide a live USD/USDT exchange-rate quote or lock. The exact USDT amount displayed on BoltUtil Hosted Checkout is the amount to pay. Network transaction fees are charged by the customer's wallet or network, separately from the invoice amount.

BoltUtil is an external service required for this plugin. Merchants must register a BoltUtil account, create a LIVE API key, configure receiving wallets and an active Webhook, and enter the key and Webhook secret in the gateway settings. The BoltUtil free plan permits up to 30 orders created per BoltUtil server day; this counts order creation, not successful payments. Other plans are described at https://boltutil.com/pricing . The service, rather than plugin code, enforces account limits.

When a customer checks out, the plugin sends the order amount, USD currency, USDT token, selected network, a store-specific external order ID, the store's return URL, and a merchant reference to BoltUtil over HTTPS. It sends the merchant API key and a signed request; it does not send the customer's wallet private key. The plugin receives signed payment events and queries BoltUtil to verify status before completing a WooCommerce order. See https://boltutil.com/privacy and https://boltutil.com/terms for BoltUtil service policies. The merchant remains responsible for their store's own privacy notice and payment disclosures.

== Installation ==

1. Install and activate WooCommerce, then install this plugin.
2. Set the WooCommerce store currency to USD.
3. In BoltUtil, create a LIVE API key, enable a USDT receiving wallet for each desired network, and create an active Webhook using the URL shown in WooCommerce > Settings > Payments > BoltUtil USDT.
4. Enter the LIVE API key and the matching Webhook secret in the gateway settings. Select networks backed by active receiving wallets, enable the gateway, and save.
5. Place an unpaid order to check the network choice, BoltUtil redirect, amount, address, and return link. Returning to the store does not mark the order paid.

== Frequently Asked Questions ==

= Does the plugin store my wallet private key? =

No. Receiving wallets are configured in BoltUtil. The plugin stores the merchant's API key and Webhook secret encrypted using the WordPress site's authentication salts. Keep the WordPress installation and its salts secure.

= Why do the credential fields show masked text after saving? =

The masked preview shows only the credential type and last four characters; the actual input value remains empty. Leaving the input blank during a later save keeps the existing credential.

= When is a WooCommerce order marked paid? =

Only after BoltUtil confirms payment and the plugin verifies the signed Webhook and payment status, or after a merchant-scoped status reconciliation. A browser redirect back to the store is not proof of payment.

= Can customers pay with USDC or on Base? =

Not in this version. Those are planned for later releases after BoltUtil supports the full asset and network payment lifecycle. This plugin does not present planned routes as available payment choices.

= Which languages are available? =

English is the fallback. The plugin includes translations for Simplified Chinese, Spanish, Brazilian Portuguese, Russian, Vietnamese, Turkish, Japanese, Korean, Arabic, French, and Hindi. The settings interface follows the administrator's WordPress language; checkout follows the store or customer locale supplied by WordPress. Merchant-written checkout labels are not translated automatically.

= What happens when a payment needs investigation? =

The WooCommerce order displays the BoltUtil payment ID, network, invoice amount, and payable amount. An administrator can use the Recheck BoltUtil payment order action. Payment exceptions should also be reviewed in the BoltUtil merchant dashboard.

= What happens if a payment expires or fails? =

When BoltUtil verifies an EXPIRED, FAILED, or CANCELLED payment, the plugin marks the unpaid WooCommerce order Failed and records the BoltUtil status in its order notes. A buyer must place a new WooCommerce order to make a new payment attempt. Any unexpected late transfer or amount discrepancy needs merchant review in BoltUtil; the plugin never marks an underpaid order paid automatically.

= Where can merchants get support? =

Use the WordPress.org support forum for plugin setup questions. For BoltUtil account, API, wallet, or payment issues, email support@boltutil.com. Do not include API keys, Webhook secrets, private keys, or recovery phrases in a support request.

== Changelog ==

= 0.4.18 =

* Publish source documentation and the GPL license, with comments explaining API signing, retries, decimal comparison, and Webhook verification. Payment behavior is unchanged.

= 0.4.17 =

* Package the CC0 icon license as a text file recognized by WordPress Plugin Check.

= 0.4.16 =

* Replace the network and USDT images with CC0 artwork distributed with a source and license notice.
* Send LIVE payment API requests only to the official BoltUtil API origin, including on upgraded stores.
* Clean release packages of editor backup files and keep credentials out of distributable ZIPs.

= 0.4.15 =

* Remove the square WooCommerce Blocks side rails around the BoltUtil payment option and use light network cards with a clear selected state.
* Add pricing, documentation, terms, and support links to the BoltUtil settings menu and installed-plugin row.

= 0.4.14 =

* Match the BoltUtil checkout network choices with chain and USDT image badges in Classic Checkout and Checkout Blocks.
* Keep the network names visible so the payment options remain identifiable when images cannot load, and show a masked credential hint without submitting it as a new key.

= 0.4.13 =

* Register WooCommerce gateway hooks once so status reconciliation does not duplicate the BoltUtil details panel or manual order actions.

= 0.4.12 =

* Mark an unpaid WooCommerce order Failed after a merchant-scoped BoltUtil status read verifies EXPIRED, FAILED, or CANCELLED, while retaining the status in an order note.
* Keep amount discrepancies for merchant review and preserve the existing completion verification path.

= 0.4.11 =

* Added bundled translations for Spanish, Brazilian Portuguese, Russian, Vietnamese, Turkish, Japanese, Korean, Arabic, French, and Hindi, alongside English source text and Simplified Chinese.
* Interface language follows the WordPress locale; English remains the fallback when a translation is unavailable.

= 0.4.10 =

* Added a complete Simplified Chinese PO/MO language pack and a bundled translation path for ZIP installations.
* Let WordPress translations control the display language, including Traditional Chinese and future community translations.

= 0.4.9 =

* Clarified a translated payment-status placeholder and removed obsolete manual text-domain loading.
* Hardened secret-field input handling and escaped the Webhook event table identifier in SQL.

= 0.4.8 =

* Prepared WordPress.org release metadata and documented the current USDT scope and external service requirements.
* Kept the existing WooCommerce gateway ID and payment behavior unchanged.
* Replaced bundled chain and token artwork with text badges for clear distribution rights.
* Made English interface strings available to standard WordPress translation tools.
