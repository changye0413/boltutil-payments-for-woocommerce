#!/usr/bin/env python3
"""Check submission metadata, shipped translations and licensed SVG provenance."""
import gettext
import hashlib
import json
from pathlib import Path
import re
import xml.etree.ElementTree as ET

ROOT = Path(__file__).resolve().parent.parent
main = (ROOT / 'boltutil-woocommerce.php').read_text()
readme = (ROOT / 'readme.txt').read_text()
version = re.search(r'^ \* Version: (.+)$', main, re.M).group(1)
assert re.search(r'^Stable tag: (.+)$', readme, re.M).group(1) == version, 'Version/stable tag mismatch'
assert "define( 'BOLTUTIL_WC_VERSION', '" + version + "' );" in main, 'Asset version mismatch'
assert re.search(r'^Contributors: [a-zA-Z0-9_-]+', readme, re.M), 'Missing publisher username'
assert all(text in readme for text in ('https://boltutil.com/pricing', 'https://boltutil.com/privacy', 'https://boltutil.com/terms', 'external service')), 'Missing service disclosure'
strings = set()
for path in [ROOT / 'boltutil-woocommerce.php', *ROOT.glob('includes/*.php')]:
    source = path.read_text()
    for text in re.findall(r"__\(\s*'((?:\\.|[^'\\])*)'\s*,\s*'boltutil-payments-for-woocommerce'", source):
        strings.add(text.replace("\\'", "'").replace('\\\\', '\\'))
    assert 'Free plan: up to 30 orders' not in source, 'Stale quota promise'
catalogs = list(ROOT.glob('languages/*.mo'))
assert len(catalogs) == 11, 'Missing bundled catalog'
for path in catalogs:
    with path.open('rb') as stream:
        catalog = gettext.GNUTranslations(stream)
    missing = strings - catalog._catalog.keys()
    assert not missing, f'{path.name}: untranslated source entries {missing}'
    assert all(catalog._catalog[text] for text in strings), f'{path.name}: empty runtime translations'
    allowance = 'Daily free allowance · Unlimited daily orders with a subscription'
    assert catalog.gettext(allowance) != allowance, f'{path.name}: untranslated allowance copy'
    assert 'Free plan: up to 30 orders created per day' not in catalog._catalog, 'Stale compiled quota copy'
manifest = json.loads((ROOT / 'assets/chains/sources.json').read_text())
documented = set()
for item in manifest:
    path = ROOT / item['file']
    assert item['license'] in ('CC0-1.0', 'GPL-2.0-or-later'), 'Unconfirmed asset license'
    assert hashlib.sha256(path.read_bytes()).hexdigest() == item['sha256'], f'Asset changed: {path}'
    assert ET.parse(path).getroot().tag == '{http://www.w3.org/2000/svg}svg', 'Invalid SVG'
    documented.add(path.resolve())
assert documented == {path.resolve() for path in ROOT.glob('assets/**/*.svg')}, 'Undocumented artwork'
print(f'Distribution checks passed: version {version}, contributor, service policies, {len(strings)} PHP strings in 11 MO catalogs, {len(documented)} licensed SVGs.')
