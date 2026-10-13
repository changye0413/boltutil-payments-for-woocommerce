# BoltUtil — WooCommerce で USDT・USDC 決済を受け付ける

[English](../../README.md) · [简体中文](README.zh-CN.md) · [Español](README.es.md) · [Português](README.pt-BR.md) · [Русский](README.ru.md) · [Tiếng Việt](README.vi.md) · [Türkçe](README.tr.md) · [日本語](README.ja.md) · [한국어](README.ko.md) · [العربية](README.ar.md) · [Français](README.fr.md) · [हिन्दी](README.hi.md)

![BoltUtil USDT / USDC](../../wordpress-org-assets/banner-1544x500.png)

プラグイン **v0.6.1** は WooCommerce と BoltUtil Hosted Checkout を連携します。購入者は通貨とネットワークを選び、表示された正確な金額を支払います。対応する端末・ネットワークでは、接続したウォレットに送金先と金額が入力され、内容を確認して承認できます。手動送金も利用できます。資金は加盟店の受取ウォレットに直接届きます。

TRON、Ethereum、BNB Smart Chain、Polygon、Solana、Base の6ネットワークに対応。USDT は6、USDC は5ネットワークで、計11通りです。TRON は USDT のみ。Base の USDT は Bridged USDT、BNB の USDC は Binance-Peg USDC です。サービス・加盟店ウォレット・プラグイン設定で有効な組み合わせだけが表示されます。

## インストールと設定

v0.6.1 のインストール用 ZIP を WordPress のプラグインアップロード画面から導入します。店舗通貨を USD に設定し、同じ BoltUtil 加盟店アカウントで LIVE API Key、受取ウォレット、有効な Webhook を設定して保存します。更新時に既存の注文と認証情報は保持されます。USDC は別途有効化してください。

**WordPress 7.0+ · PHP 7.4+ · WooCommerce 11.1+ · HTTPS · USD**

## 言語と決済について

表示言語は WordPress のロケールに従います。加盟店が入力した独自の文言は自動翻訳されません。毎日の無料枠があり、サブスクリプションでは1日あたりの注文数制限がありません。最新の条件・料金は公式サイトをご確認ください。決済画面の正確な金額を支払い、ネットワーク手数料は別途負担します。店舗へのリダイレクトだけでは支払済みにならず、署名と決済状態を検証します。

デモの閲覧は無料ですが、少額の購入には実際の資金を使用します。 WordPress.org のプラグインディレクトリで公開済みです。

[Download v0.6.1](https://github.com/changye0413/boltutil-payments-for-woocommerce/releases/tag/v0.6.1) · [Live demo](https://shop-test.boltutil.com/) · [Integration guide](https://boltutil.com/woocommerce) · [Pricing](https://boltutil.com/pricing)

[Full setup and payment contract](../../README.md#configure-in-five-steps) · [Language guide](../I18N.md) · [Security](../../SECURITY.md)

![USDC checkout preview](../../wordpress-org-assets/screenshot-2.jpg)

[WordPress.org](https://wordpress.org/plugins/boltutil-payments-for-woocommerce/)

## ウォレット決済と手動送金

![ウォレット決済のプレビュー](../../wordpress-org-assets/screenshot-5.jpg)

![手動送金のプレビュー](../../wordpress-org-assets/screenshot-6.jpg)

画像はデモデータを使用しています。表示されたアドレスと QR コードで支払うことはできません。利用できるウォレットはネットワークと端末によって異なります。
