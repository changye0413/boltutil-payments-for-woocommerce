#!/usr/bin/env bash
set -euo pipefail

plugin_dir="$(cd "$(dirname "$0")/.." && pwd)"
project_dir="$(cd "$plugin_dir/.." && pwd)"
main_file="$plugin_dir/boltutil-woocommerce.php"
readme_file="$plugin_dir/readme.txt"
release_slug="boltutil-payments-for-woocommerce"
mode="${1:---release}"
if [[ "$mode" != "--release" && "$mode" != "--preview" ]]; then
    printf 'Usage: %s [--release|--preview]\n' "$0" >&2
    exit 1
fi

version="$(sed -n 's/^ \* Version: //p' "$main_file" | head -1)"
stable="$(sed -n 's/^Stable tag: //p' "$readme_file" | head -1)"
if [[ -z "$version" || "$version" != "$stable" ]]; then
    printf 'Plugin version and readme Stable tag must match.\n' >&2
    exit 1
fi

release_dir="$(mktemp -d "${TMPDIR:-/tmp}/boltutil-wc-release.XXXXXX")"
trap 'rm -rf "$release_dir"' EXIT
mkdir -p "$release_dir/$release_slug"
cp "$main_file" "$release_dir/$release_slug/$release_slug.php"
cp "$readme_file" "$plugin_dir/ASSETS-LICENSE.txt" "$plugin_dir/CRYPTO-ICONS-CC0-LICENSE.txt" "$release_dir/$release_slug/"
cp -R "$plugin_dir/includes" "$plugin_dir/assets" "$plugin_dir/languages" "$release_dir/$release_slug/"
# Editors can leave backup catalogs beside the real PO files. They are not
# runtime assets and Plugin Check rejects their trailing-tilde filenames.
find "$release_dir/$release_slug/languages" -type f -name '*~' -delete

# A public package may contain only the six pinned CC0 chain/token SVGs.
if find "$release_dir/$release_slug/assets" -type f \( -name '*.png' -o -name '*.jpg' -o -name '*.jpeg' \) | grep -q .; then
    printf 'Unlicensed raster asset found in release package.\n' >&2
    exit 1
fi
if grep -RIlE 'bt_live_[A-Za-z0-9_-]{20,}|whsec_[A-Za-z0-9_-]{20,}|api-test\.boltutil\.com|192\.168\.[0-9]+' "$release_dir/$release_slug" | grep -q .; then
    printf 'Potential credential or test-only origin found in release package.\n' >&2
    exit 1
fi

suffix=""
if [[ "$mode" == "--preview" ]]; then
    suffix="-preview"
fi
archive="$project_dir/$release_slug-$version$suffix.zip"
temporary_archive="$release_dir/$release_slug-$version.zip"
( cd "$release_dir" && zip -q -r "$temporary_archive" "$release_slug" )
unzip -tq "$temporary_archive"
mv -f "$temporary_archive" "$archive"
shasum -a 256 "$archive"
printf 'Package: %s\n' "$archive"
