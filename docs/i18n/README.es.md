# BoltUtil — Acepta USDT y USDC en WooCommerce

[English](../../README.md) · [简体中文](README.zh-CN.md) · [Español](README.es.md) · [Português](README.pt-BR.md) · [Русский](README.ru.md) · [Tiếng Việt](README.vi.md) · [Türkçe](README.tr.md) · [日本語](README.ja.md) · [한국어](README.ko.md) · [العربية](README.ar.md) · [Français](README.fr.md) · [हिन्दी](README.hi.md)

![BoltUtil USDT / USDC](../../wordpress-org-assets/banner-1544x500.png)

El plugin **v0.6.1** conecta WooCommerce con BoltUtil Hosted Checkout. El cliente elige una moneda y una red, y paga el importe exacto mostrado. En dispositivos y redes compatibles, una cartera conectada completa la dirección y el importe para revisarlos y confirmar. También se puede hacer una transferencia manual. Los fondos llegan a la cartera configurada por el comerciante.

Seis redes: TRON, Ethereum, BNB Smart Chain, Polygon, Solana y Base. USDT está disponible en seis y USDC en cinco: once combinaciones. TRON solo admite USDT; el USDT de Base es Bridged USDT y el USDC de BNB es Binance-Peg USDC. Solo aparecen las rutas habilitadas por el servicio, la cartera del comerciante y el plugin.

## Instalación y configuración

Descarga el ZIP instalable de v0.6.1 y súbelo desde Plugins de WordPress. Configura la moneda de la tienda en USD. En la misma cuenta de comerciante de BoltUtil, crea una clave API LIVE, configura las carteras receptoras y un Webhook activo; guarda las credenciales y las redes en el plugin. La actualización conserva pedidos y credenciales; USDC debe habilitarse expresamente.

**WordPress 7.0+ · PHP 7.4+ · WooCommerce 11.1+ · HTTPS · USD**

## Idiomas y pagos

WordPress selecciona el idioma de la interfaz. Las etiquetas escritas por el comerciante no se traducen automáticamente. Hay una cuota gratuita diaria y los planes de suscripción no tienen límite diario de pedidos; consulta las condiciones actuales en BoltUtil. Paga el importe exacto del checkout; las comisiones de red son independientes. Volver a la tienda no confirma el pago: el plugin verifica la firma y el estado antes de completar el pedido.

La demo puede explorarse gratis; las compras pequeñas usan fondos reales. Disponible en el directorio de plugins de WordPress.org.

[Download v0.6.1](https://github.com/changye0413/boltutil-payments-for-woocommerce/releases/tag/v0.6.1) · [Live demo](https://shop-test.boltutil.com/) · [Integration guide](https://boltutil.com/woocommerce) · [Pricing](https://boltutil.com/pricing)

[Full setup and payment contract](../../README.md#configure-in-five-steps) · [Language guide](../I18N.md) · [Security](../../SECURITY.md)

![USDC checkout preview](../../wordpress-org-assets/screenshot-2.jpg)

[WordPress.org](https://wordpress.org/plugins/boltutil-payments-for-woocommerce/)

## Pago con cartera y transferencia manual

![Vista previa del pago con cartera](../../wordpress-org-assets/screenshot-5.jpg)

![Vista previa de transferencia manual](../../wordpress-org-assets/screenshot-6.jpg)

Las vistas del checkout usan datos de demostración. La dirección y el QR no permiten pagar. La disponibilidad de carteras depende de la red y el dispositivo.
