# BoltUtil — Receba USDT e USDC no WooCommerce

[English](../../README.md) · [简体中文](README.zh-CN.md) · [Español](README.es.md) · [Português](README.pt-BR.md) · [Русский](README.ru.md) · [Tiếng Việt](README.vi.md) · [Türkçe](README.tr.md) · [日本語](README.ja.md) · [한국어](README.ko.md) · [العربية](README.ar.md) · [Français](README.fr.md) · [हिन्दी](README.hi.md)

![BoltUtil USDT / USDC](../../wordpress-org-assets/banner-1544x500.png)

O plugin **v0.6.1** conecta o WooCommerce ao BoltUtil Hosted Checkout. O cliente escolhe a moeda e a rede e paga o valor exato exibido. Em dispositivos e redes compatíveis, a carteira conectada preenche o endereço e o valor para revisão e confirmação. A transferência manual também está disponível. Os fundos chegam à carteira configurada pelo lojista.

São seis redes: TRON, Ethereum, BNB Smart Chain, Polygon, Solana e Base. USDT em seis e USDC em cinco, totalizando onze combinações. TRON aceita apenas USDT; na Base o USDT é Bridged USDT e na BNB o USDC é Binance-Peg USDC. A disponibilidade depende do serviço, das carteiras do lojista e das opções do plugin.

## Instalação e configuração

Baixe o ZIP instalável da v0.6.1 e envie-o pela página de plugins do WordPress. Defina a moeda da loja como USD. Na mesma conta de lojista BoltUtil, crie uma chave API LIVE, configure as carteiras e um Webhook ativo; salve as credenciais e as redes no plugin. A atualização preserva pedidos e credenciais; o USDC precisa ser habilitado.

**WordPress 7.0+ · PHP 7.4+ · WooCommerce 11.1+ · HTTPS · USD**

## Idiomas e pagamentos

O WordPress define o idioma da interface. Textos personalizados do lojista não são traduzidos automaticamente. Há uma franquia gratuita diária, e as assinaturas não têm limite diário de pedidos; consulte os benefícios e custos atuais no site. Use o valor exato do checkout; as taxas de rede são separadas. Retornar à loja não comprova o pagamento: o plugin verifica a assinatura e o status antes de concluir o pedido.

A navegação na demo é gratuita; pequenas compras usam fundos reais. Disponível no diretório de plugins do WordPress.org.

[Download v0.6.1](https://github.com/changye0413/boltutil-payments-for-woocommerce/releases/tag/v0.6.1) · [Live demo](https://shop-test.boltutil.com/) · [Integration guide](https://boltutil.com/woocommerce) · [Pricing](https://boltutil.com/pricing)

[Full setup and payment contract](../../README.md#configure-in-five-steps) · [Language guide](../I18N.md) · [Security](../../SECURITY.md)

![USDC checkout preview](../../wordpress-org-assets/screenshot-2.jpg)

[WordPress.org](https://wordpress.org/plugins/boltutil-payments-for-woocommerce/)

## Pagamento com carteira e transferência manual

![Prévia do pagamento com carteira](../../wordpress-org-assets/screenshot-5.jpg)

![Prévia de transferência manual](../../wordpress-org-assets/screenshot-6.jpg)

As imagens usam dados de demonstração. O endereço e o QR não permitem pagamentos. A disponibilidade depende da rede e do dispositivo.
