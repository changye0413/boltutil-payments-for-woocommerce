# WordPress.org listing artwork

These files belong in the **top-level `assets/` directory of the assigned WordPress.org SVN repository**, not in the plugin ZIP or under `trunk/assets/`.

`brand-mark-source.png` is copied from the BoltUtil frontend's `public/logo.png`. It is the existing BoltUtil-owned bolt silhouette. `tools/build-directory-assets.py` uses that exact alpha silhouette to render the black and white icon and the banner. The banner text is part of the directory artwork only. BoltUtil releases this artwork under GPLv2 or later for use with this plugin.

Run `python3 tools/build-directory-assets.py` from the plugin repository root with Pillow installed to regenerate. The output filenames and pixel dimensions follow the [WordPress.org plugin asset specification](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/).

The WordPress.org plugin directory has not yet assigned a repository. Keep these assets in GitHub until submission is approved.

The v0.6.1 banner shows USDT / USDC, six networks, and wallet confirmation or manual transfer. English and `zh_CN` variants are provided. Four `screenshot-*.jpg` images are real interface captures from the public demo store and the merchant setup guide, with matching captions in readme.txt. Captures deliberately omit credential fields, personal account details and bearer tokens. The storefront includes BoltUtil-owned AI-generated artwork; no artist attribution or physical-shipping claim is made. WordPress and WooCommerce interface imagery is shown to document this integration; their marks do not imply endorsement.
