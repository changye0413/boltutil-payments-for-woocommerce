# Contributing

Thanks for helping improve BoltUtil Payments for WooCommerce. Small, focused changes are easiest to review.

## Local setup

1. Use a separate WordPress test store with WooCommerce, PHP 7.4 or newer, and HTTPS. Keep the store currency USD.
2. Install the plugin ZIP built by `bash tools/build-release.sh`, or mount this source directory as `boltutil-payments-for-woocommerce` under `wp-content/plugins/` for development.
3. Use your own BoltUtil merchant account and receiving addresses. Never commit credentials, production exports, wallet private keys, or recovery phrases.

## Checks before a pull request

```bash
# Requires a PHP CLI.
for file in boltutil-woocommerce.php includes/*.php tests/*.php; do php -l "$file"; done
php tests/webhook-fixtures.php

# Requires Node.js and gettext.
for file in assets/*.js; do node --check "$file"; done
for file in languages/*.po; do msgfmt --check -o /dev/null "$file"; done
bash tools/build-release.sh
```

Run WordPress Plugin Check on an installed build. Check classic checkout and Checkout Blocks. Changes to payment creation, signature verification, status transitions, or event deduplication also need a test showing a rejected case and a successful case. A simulated fixture does not replace a small real payment acceptance test.

GitHub Actions checks the advertised PHP 7.4 floor and PHP 8.3, verifies the isolated Webhook fixtures, parses JavaScript and all PO catalogs, and inspects the installable ZIP. WordPress.org listing artwork is in `wordpress-org-assets/` and must be copied to the top-level SVN `assets/` directory only after the plugin is approved; it is not part of the installable ZIP.

## Design boundaries

- The plugin calls the BoltUtil Public Payment API and uses BoltUtil Hosted Checkout. Do not copy blockchain monitors or payment matching into the plugin.
- Keep money as decimal strings in the PHP adapter; do not use floating-point arithmetic for payment amounts.
- Never complete a WooCommerce order from a browser return alone. Verify the Webhook and query payment status with the merchant's API key.
- Keep translations in WordPress PO/MO format. English is the source language.
- Keep all bundled assets GPL-compatible and document their provenance.

Please open an issue before a broad feature or data-model change. See [SECURITY.md](SECURITY.md) for private vulnerability reports.
