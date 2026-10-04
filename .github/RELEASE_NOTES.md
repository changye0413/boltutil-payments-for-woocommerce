# v0.5.2 — USDT, USDC and Base

Install `boltutil-payments-for-woocommerce-0.5.2.zip` via **WordPress → Plugins → Add New → Upload Plugin**. The GitHub source archives are not the installable plugin package.

## Changes

- Add merchant-controlled USDT/USDC selection and service-authorized asset/network routes in Classic Checkout and Checkout Blocks.
- Add Base native USDC and explicitly labeled Bridged USDT.
- Add native USDC on Ethereum, Polygon PoS and Solana, and explicitly labeled Binance-Peg USDC on BNB Smart Chain.
- Preserve USDT-only defaults on upgrades and reject unsupported or inactive routes.
- Verify stored token/network identity during Webhook handling and payment reconciliation; keep decimal amounts as strings.
- Include Base/USDC artwork with license notices and update multilingual configuration guidance.
- Retain repeated scheduled payment checks, manual rechecks and verified expiry handling.

## Setup and compatibility

Requires WordPress 6.5+, PHP 7.4+, WooCommerce 11.1+, HTTPS, USD store currency, a LIVE BoltUtil API key, a matching active Webhook and configured receiving wallets. Update the BoltUtil service to its multi-asset version and enable each tested route before offering it. Installing this ZIP does not activate network monitoring or USDC automatically.

Read the [setup guide](https://github.com/changye0413/boltutil-payments-for-woocommerce#configure-in-five-steps). Pay the exact token amount on BoltUtil Hosted Checkout; network fees are separate.

**Early release:** local fixtures cover signed Webhooks, reconciliation scheduling and asset/network isolation. Previous test-store checks covered order creation and redirect behavior. A real multi-asset WooCommerce payment followed by a production completion Webhook has not yet been verified end to end. Complete small-amount acceptance tests before normal sales or WordPress.org submission.

The release includes `SHA256SUMS.txt` for download integrity verification.
