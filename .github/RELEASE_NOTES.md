**Early release.** Install this ZIP via **WordPress → Plugins → Add New → Upload Plugin**. It provides USDT checkout on five networks, BoltUtil Hosted Checkout redirection, signed Webhook verification, and merchant-scoped payment status checks.

Version 0.4.21 fixes scheduled reconciliation for unpaid orders: a running check now queues its next check until BoltUtil returns a verified final status. Failed status lookups write a safe WooCommerce log warning. A payment that cannot be verified remains unpaid for merchant review; the plugin does not infer failure from elapsed time or an API error.

Requirements: WordPress 6.5+, PHP 7.4+, WooCommerce 11.1+, HTTPS, a USD store, a LIVE BoltUtil API key, a matching active Webhook secret, and configured receiving wallets. See the [setup guide](https://github.com/changye0413/boltutil-payments-for-woocommerce#configure-in-five-steps).

Order creation, Hosted Checkout redirect, and repeated scheduled checks were verified on a test store. Seven older unpaid orders moved to Failed only after BoltUtil returned `EXPIRED`; three older orders returned HTTP 404 under the current merchant credentials and remain On hold for review. A real WooCommerce-originated blockchain payment with a production completion Webhook has not yet been verified end to end. Test with a small amount before normal sales.
