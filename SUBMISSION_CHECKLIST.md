# WordPress.org submission checklist

This file is a WordPress.org submission gate for BoltUtil Payments for WooCommerce. It is not a claim that a real payment has passed. The current GitHub early release is **0.6.0**; WordPress.org submission remains gated on real-payment acceptance.

## Completed preparation

- [x] The plugin ZIP has the required slug and matching main PHP filename, a GPLv2-or-later license, WordPress.org `readme.txt`, privacy and terms links, service disclosure, support route, installation steps, and explicit USD/stablecoin limitations.
- [x] Supported USDT/USDC routes are explicitly listed, including Base native USDC, Bridged USDT and BEP20 Binance-Peg USDC. Only service-authorized routes with merchant receiving wallets are offered; upgrades retain USDT-only defaults.
- [x] The candidate declares the tested WooCommerce **11.1** line and Cart and Checkout Blocks and HPOS compatibility. The live payment flow still needs acceptance below.
- [ ] Confirm WordPress.org-compatible redistribution permissions for the official Polygon, BNB Chain and Base SVGs included in 0.6.0. Source URLs and exact hashes are recorded; these assets must not be described as CC0 or BoltUtil-owned GPL artwork. WordPress.org icon and banner source files are separate from the installable ZIP.
- [x] English is the source language. Eleven bundled translations parse. Merchant-written checkout labels remain controlled by the merchant and are not auto-translated.
- [x] The build script rejects obvious live credentials and test-only origins. PHP syntax, JavaScript syntax, translation catalogs, the isolated signed Webhook fixture, ZIP integrity, and WordPress.org Readme Validator were checked locally. The Readme Validator still notes the missing WordPress.org `Contributors` field because the publisher account does not yet exist; screenshots and a donate link are optional listing enhancements.
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
2. Add the **actual WordPress.org username** to `Contributors:` in `readme.txt`. Never substitute the test-store administrator name or GitHub username without verifying it is the same WordPress.org account.
3. Rebuild the candidate, rerun PHP/JS/PO checks, Readme Validator, Plugin Check on the exact ZIP, and the package credential scan. Refresh `Tested up to` / `WC tested up to` only if newly tested versions justify it.
4. After the real-payment gate passes, update release notes from "pending" to the observed acceptance result and tag/publish the final GitHub ZIP. Record its SHA-256.
5. Submit that exact installable ZIP through [WordPress.org plugin submission](https://wordpress.org/plugins/developers/add/). Answer reviewer questions with the published code and evidence; do not claim WordPress.org approval before it is granted.
6. Once the directory assigns a slug and SVN repository, copy the plugin code to `trunk/`, create a matching version tag under `tags/`, and copy `wordpress-org-assets/icon-*.png` and `banner-*.png` to the **top-level** SVN `assets/` directory. Do not copy `brand-mark-source.png`, the asset README, tests, tools, GitHub files, or internal acceptance notes into the installable plugin.
7. Verify the public listing, install/update from WordPress.org, compatibility display, translation behavior, support page, and live ZIP contents before announcing directory availability.

Directory review and approval are controlled by WordPress.org and cannot be guaranteed by static tools. Keep every enabled asset/network claim tied to observed acceptance evidence.
