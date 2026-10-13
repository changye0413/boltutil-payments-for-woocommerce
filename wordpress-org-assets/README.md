# WordPress.org listing artwork

These files belong in the **top-level `assets/` directory of the assigned WordPress.org SVN repository**, not in the plugin ZIP or under `trunk/assets/`.

`brand-mark-source.png` is the existing BoltUtil-owned bolt silhouette copied from the frontend's `public/logo.png`. BoltUtil releases its directory artwork under GPLv2 or later for use with this plugin.

The banner selected on 2026-10-13 uses AI-generated artwork with BoltUtil, USDT / USDC, six networks, wallet payment and manual transfer. It has no version label. Default and `zh_CN` filenames currently share the same selected English artwork; translated README text remains separate. Sizes are 1544 × 500 and 772 × 250 pixels.

The approved banner assets are published in GitHub and WordPress.org SVN revision 3742959. Public listing: https://wordpress.org/plugins/boltutil-payments-for-woocommerce/. This assets-only update did not change the published 0.6.1 code or tag. Directory/CDN caches may refresh after the commit.

`tools/build-directory-assets.py` produces the earlier programmatic banner and icons. Do not overwrite these selected banners with its output; preserve them when regenerating icons. Filenames and dimensions follow the [WordPress.org plugin asset specification](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/).

Four `screenshot-*.jpg` images are real interface captures from the public demo store and merchant setup guide. They omit credential fields, personal account details and bearer tokens. The storefront includes AI-generated digital artwork; no physical shipping is claimed. Brand names and interface imagery identify integrations and do not imply endorsement.
