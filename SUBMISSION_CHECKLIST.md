# WordPress.org submission checklist

This file is a WordPress.org submission gate for BoltUtil Payments for WooCommerce. The local submission candidate is **0.6.1**. It has not been published in this task. On 2026-10-12 the merchant confirmed that real-payment acceptance has not yet been completed; submission remains gated on the acceptance matrix below.

## Completed preparation

- [x] The plugin ZIP has the required slug and matching main PHP filename, a GPLv2-or-later license, WordPress.org `readme.txt`, privacy and terms links, service disclosure, support route, installation steps, and explicit USD/stablecoin limitations.
- [x] Supported USDT/USDC routes are explicitly listed, including Base native USDC, Bridged USDT and BEP20 Binance-Peg USDC. Only service-authorized routes with merchant receiving wallets are offered; upgrades retain USDT-only defaults.
- [x] The candidate declares the tested WooCommerce **11.1** line and Cart and Checkout Blocks and HPOS compatibility. The live payment flow still needs acceptance below.
- [x] The merchant authorized replacing the uncertain Polygon, BNB Chain and Base brand-kit assets. 0.6.1 bundles pinned CC0 BNB Chain/Polygon artwork and an original GPL generic Base network illustration. Chain names remain visible; all nine bundled SVGs have source/license/hash metadata. Listing artwork remains outside the ZIP.
- [x] English is the source language. Eleven bundled translations parse. Merchant-written checkout labels remain controlled by the merchant and are not auto-translated.
- [x] Publisher username `boltutildev` is recorded in `Contributors`, as confirmed by the merchant. `BoltUtil` is the display name, not the contributor identifier.
- [x] 0.6.1 passes PHP 7.4/8.3 WebAssembly syntax and fixture checks, JavaScript syntax/checkout tests, all eleven PO/MO catalogs, 84 runtime PHP strings, asset provenance checks and release credential/file checks. The dedicated Plugin Check 2.1.0 static and runtime runs report **0 errors / 26 warnings** on WordPress 7.1.3, WooCommerce 11.1.0 and PHP 8.3.33. Warnings are documented nonce delegations, atomic database operations and bundled translation registration; they remain visible in the saved results.
- [x] Actual WordPress/WooCommerce CRUD and REST smoke tests pass 51 assertions with simulated BoltUtil service replies across all eleven routes, duplicate callbacks and final failure/underpayment statuses. The environment uses SQLite and does not contact a blockchain or send email. This is separate from real payment, MySQL concurrency and production delivery acceptance.
- [x] The WordPress requirement now matches WooCommerce 11.1's own floor: WordPress 7.0+. The compatibility smoke test used WordPress 7.1.3, not a full WordPress/PHP version matrix.
- [x] On the test store, 0.4.19 upgraded in place, stayed active, preserved the configured merchant credentials and five networks, and rendered all five options in Checkout Blocks and a draft Classic Checkout QA page. Changing the network kept one selected radio in each checkout type. No order or transfer was made in this smoke test. Plugin Check 2.1.0 reported **0 errors and 22 warnings**, the same count as 0.4.17. See the private release checklist for warning categories and test evidence.
- [x] On the test store, 0.4.21 upgraded in place and remained active. Previously stranded unpaid orders were manually rechecked: verified `EXPIRED` payments changed to Failed; three older payments returned HTTP 404 under the currently configured merchant key and were left On hold for merchant review. A new unpaid $1 order redirected to BoltUtil Hosted Checkout; its first scheduled check completed and queued a second check five minutes later. No blockchain transfer was made.

Confirm the PHP 7.4/8.3 CI matrix and the downloadable release ZIP before calling the public repository synchronized.

## Real-payment acceptance — merchant to perform before submission

For each enabled asset/network combination, use a **new WooCommerce order** and a small real payment. The merchant records evidence privately, not in this public repository. Do not post credentials, receiving addresses, or customer information in GitHub issues.

| Asset / network | New Woo order + Hosted Checkout | Exact address/amount verified before sending | Confirmed BoltUtil payment | Signed Webhook delivered | Woo order paid exactly once | Return page checked |
| --- | --- | --- | --- | --- | --- | --- |
| USDT / TRON (TRC20) | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |
| USDT / Ethereum (ERC20) | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |
| USDC / Ethereum (ERC20) | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |
| USDT / BNB Smart Chain (BEP20) | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |
| Binance-Peg USDC / BNB Smart Chain (BEP20) | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |
| USDT / Polygon PoS | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |
| USDC / Polygon PoS | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |
| USDT / Solana | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |
| USDC / Solana | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |
| Bridged USDT / Base | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |
| USDC / Base | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |

For one representative payment, also confirm that repeating the same signed event does not create a second completion or order note. Verify an expired unpaid order becomes Failed after a merchant-scoped status read. An underpaid or mismatched payment must stay unpaid and be visible for merchant review. Confirm the scheduled reconciliation path in the actual WordPress/MySQL/Action Scheduler environment, and verify WooCommerce customer emails after mail transport is configured. A browser return alone must never complete an order.

## Publisher account and directory submission — after acceptance

1. Create and verify a [WordPress.org account](https://login.wordpress.org/register). The publisher chooses the account identity and completes any required terms or anti-bot challenge personally.
2. Verify the submitted account is `boltutildev`, matching `Contributors:`. Never substitute the display name, test-store administrator name or GitHub username.
3. Rebuild the candidate, rerun PHP/JS/PO checks, Readme Validator, Plugin Check on the exact ZIP, and the package credential scan. Refresh `Tested up to` / `WC tested up to` only if newly tested versions justify it.
4. After the real-payment gate passes, update release notes from "pending" to the observed acceptance result and tag/publish the final GitHub ZIP. Record its SHA-256.
5. Submit that exact installable ZIP through [WordPress.org plugin submission](https://wordpress.org/plugins/developers/add/). Answer reviewer questions with the published code and evidence; do not claim WordPress.org approval before it is granted.
6. Once the directory assigns a slug and SVN repository, copy the plugin code to `trunk/`, create a matching version tag under `tags/`, and copy `wordpress-org-assets/icon-*.png` and `banner-*.png` to the **top-level** SVN `assets/` directory. Do not copy `brand-mark-source.png`, the asset README, tests, tools, GitHub files, or internal acceptance notes into the installable plugin.
7. Verify the public listing, install/update from WordPress.org, compatibility display, translation behavior, support page, and live ZIP contents before announcing directory availability.

Directory review and approval are controlled by WordPress.org and cannot be guaranteed by static tools. Keep every enabled asset/network claim tied to observed acceptance evidence.
