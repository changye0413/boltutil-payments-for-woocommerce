# BoltUtil — قبول USDT وUSDC في WooCommerce

[English](../../README.md) · [简体中文](README.zh-CN.md) · [Español](README.es.md) · [Português](README.pt-BR.md) · [Русский](README.ru.md) · [Tiếng Việt](README.vi.md) · [Türkçe](README.tr.md) · [日本語](README.ja.md) · [한국어](README.ko.md) · [العربية](README.ar.md) · [Français](README.fr.md) · [हिन्दी](README.hi.md)

![BoltUtil USDT / USDC](../../wordpress-org-assets/banner-1544x500.png)

<div dir="rtl">

يربط الإصدار **v0.6.1** من الإضافة WooCommerce بخدمة BoltUtil Hosted Checkout. يختار العميل العملة والشبكة ثم يدفع المبلغ الدقيق المعروض. على الأجهزة والشبكات المدعومة، تملأ المحفظة المتصلة عنوان الاستلام والمبلغ ليتم مراجعتهما وتأكيد المعاملة. يتوفر التحويل اليدوي أيضًا. تصل الأموال مباشرة إلى محفظة التاجر المحددة.

ست شبكات: TRON وEthereum وBNB Smart Chain وPolygon وSolana وBase. تدعم USDT ست شبكات وUSDC خمسًا، بإجمالي إحدى عشرة توليفة. تدعم TRON عملة USDT فقط. تستخدم Base إصدار Bridged USDT، وتستخدم BNB إصدار Binance-Peg USDC. لا تظهر إلا المسارات المفعلة في الخدمة ومحافظ التاجر وإعدادات الإضافة.

## التثبيت والإعداد

نزّل ملف ZIP القابل للتثبيت للإصدار v0.6.1 وارفعه من صفحة إضافات WordPress. اضبط عملة المتجر على USD. أنشئ LIVE API Key ومحافظ الاستلام وWebhook نشطًا في حساب التاجر نفسه لدى BoltUtil، ثم احفظ بيانات الاعتماد والشبكات في الإضافة. تحتفظ الترقية بالطلبات وبيانات الاعتماد، ويجب تفعيل USDC بصورة منفصلة.

**WordPress 7.0+ · PHP 7.4+ · WooCommerce 11.1+ · HTTPS · USD**

## اللغات والدفع

يحدد WordPress لغة الواجهة. لا تُترجم النصوص الخاصة التي يكتبها التاجر تلقائيًا. تتوفر حصة مجانية يومية، ولا تضع الاشتراكات حدًا يوميًا لعدد الطلبات. راجع الموقع لمعرفة المزايا والرسوم الحالية. ادفع المبلغ الدقيق المعروض؛ رسوم الشبكة منفصلة. العودة إلى المتجر ليست إثباتًا للدفع: تتحقق الإضافة من التوقيع وحالة الدفع قبل إكمال الطلب.

تصفح المتجر التجريبي مجاني، لكن المشتريات الصغيرة تستخدم أموالًا حقيقية. الإضافة متاحة على GitHub وقد أُرسلت إلى WordPress.org للمراجعة.

</div>

[Download v0.6.1](https://github.com/changye0413/boltutil-payments-for-woocommerce/releases/tag/v0.6.1) · [Live demo](https://shop-test.boltutil.com/) · [Integration guide](https://boltutil.com/woocommerce) · [Pricing](https://boltutil.com/pricing)

[Full setup and payment contract](../../README.md#configure-in-five-steps) · [Language guide](../I18N.md) · [Security](../../SECURITY.md)

![USDC checkout preview](../../wordpress-org-assets/screenshot-2.jpg)
