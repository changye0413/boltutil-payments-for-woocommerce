**Early release.** Install this ZIP via **WordPress → Plugins → Add New → Upload Plugin**. It provides USDT checkout on five networks, BoltUtil Hosted Checkout redirection, signed Webhook verification, and merchant-scoped payment status checks.

Requirements: WordPress 6.5+, PHP 7.4+, WooCommerce, HTTPS, a USD store, a LIVE BoltUtil API key, a matching active Webhook secret, and configured receiving wallets. See the [setup guide](https://github.com/changye0413/boltutil-payments-for-woocommerce#configure-in-five-steps).

Order creation and Hosted Checkout redirect were checked on a test store. A real WooCommerce-originated blockchain payment with a production completion Webhook has not yet been verified end to end. Test with a small amount before normal sales.
