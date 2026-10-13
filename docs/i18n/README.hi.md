# BoltUtil — WooCommerce में USDT और USDC भुगतान स्वीकार करें

[English](../../README.md) · [简体中文](README.zh-CN.md) · [Español](README.es.md) · [Português](README.pt-BR.md) · [Русский](README.ru.md) · [Tiếng Việt](README.vi.md) · [Türkçe](README.tr.md) · [日本語](README.ja.md) · [한국어](README.ko.md) · [العربية](README.ar.md) · [Français](README.fr.md) · [हिन्दी](README.hi.md)

![BoltUtil USDT / USDC](../../wordpress-org-assets/banner-1544x500.png)

प्लगइन **v0.6.1** WooCommerce को BoltUtil Hosted Checkout से जोड़ता है। ग्राहक टोकन और नेटवर्क चुनकर दिखाई गई सटीक राशि का भुगतान करता है। समर्थित डिवाइस और नेटवर्क पर जुड़ा हुआ वॉलेट पता और राशि भर देता है; ग्राहक जाँचकर लेनदेन की पुष्टि करता है। मैन्युअल ट्रांसफ़र भी उपलब्ध है। धन सीधे व्यापारी के निर्धारित वॉलेट में पहुँचता है।

छह नेटवर्क: TRON, Ethereum, BNB Smart Chain, Polygon, Solana और Base। USDT छह और USDC पाँच नेटवर्क पर उपलब्ध हैं, कुल ग्यारह संयोजन। TRON केवल USDT का समर्थन करता है। Base पर USDT, Bridged USDT है और BNB पर USDC, Binance-Peg USDC है। केवल सेवा, व्यापारी के वॉलेट और प्लगइन में सक्रिय रूट दिखते हैं।

## इंस्टॉलेशन और सेटअप

v0.6.1 का इंस्टॉल करने योग्य ZIP डाउनलोड करके WordPress के प्लगइन अपलोड पेज से इंस्टॉल करें। स्टोर की मुद्रा USD रखें। एक ही BoltUtil व्यापारी खाते में LIVE API Key, प्राप्तकर्ता वॉलेट और सक्रिय Webhook बनाकर प्लगइन में विवरण और नेटवर्क सहेजें। अपडेट पुराने ऑर्डर और क्रेडेंशियल सुरक्षित रखता है; USDC अलग से सक्रिय करना होगा।

**WordPress 7.0+ · PHP 7.4+ · WooCommerce 11.1+ · HTTPS · USD**

## भाषाएँ और भुगतान

इंटरफ़ेस की भाषा WordPress तय करता है। व्यापारी के अपने लिखे लेबल का स्वचालित अनुवाद नहीं होता। रोज़ाना मुफ़्त कोटा मिलता है और सब्सक्रिप्शन में दैनिक ऑर्डर सीमा नहीं है; मौजूदा सुविधाएँ और शुल्क वेबसाइट पर देखें। Checkout की सटीक राशि भेजें; नेटवर्क शुल्क अलग हैं। स्टोर पर वापस आने से भुगतान पूरा नहीं माना जाता: प्लगइन हस्ताक्षर और भुगतान की स्थिति जाँचता है।

डेमो देखना मुफ़्त है, लेकिन छोटी खरीद में असली धन लगता है। प्लगइन GitHub पर उपलब्ध है और समीक्षा के लिए WordPress. WordPress.org प्लगइन डायरेक्टरी में प्रकाशित है।

[Download v0.6.1](https://github.com/changye0413/boltutil-payments-for-woocommerce/releases/tag/v0.6.1) · [Live demo](https://shop-test.boltutil.com/) · [Integration guide](https://boltutil.com/woocommerce) · [Pricing](https://boltutil.com/pricing)

[Full setup and payment contract](../../README.md#configure-in-five-steps) · [Language guide](../I18N.md) · [Security](../../SECURITY.md)

![USDC checkout preview](../../wordpress-org-assets/screenshot-2.jpg)

[WordPress.org](https://wordpress.org/plugins/boltutil-payments-for-woocommerce/)

## वॉलेट भुगतान और मैनुअल ट्रांसफ़र

![वॉलेट भुगतान का पूर्वावलोकन](../../wordpress-org-assets/screenshot-5.jpg)

![मैनुअल ट्रांसफ़र का पूर्वावलोकन](../../wordpress-org-assets/screenshot-6.jpg)

चित्र डेमो डेटा का उपयोग करते हैं। दिखाए गए पते और QR कोड से भुगतान नहीं किया जा सकता। वॉलेट की उपलब्धता नेटवर्क और डिवाइस पर निर्भर करती है।
