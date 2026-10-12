# BoltUtil — Nhận USDT và USDC trong WooCommerce

[English](../../README.md) · [简体中文](README.zh-CN.md) · [Español](README.es.md) · [Português](README.pt-BR.md) · [Русский](README.ru.md) · [Tiếng Việt](README.vi.md) · [Türkçe](README.tr.md) · [日本語](README.ja.md) · [한국어](README.ko.md) · [العربية](README.ar.md) · [Français](README.fr.md) · [हिन्दी](README.hi.md)

![BoltUtil USDT / USDC](../../wordpress-org-assets/banner-1544x500.png)

Plugin **v0.6.1** kết nối WooCommerce với BoltUtil Hosted Checkout. Khách chọn đồng tiền và mạng rồi trả đúng số tiền hiển thị. Trên thiết bị và mạng được hỗ trợ, ví kết nối tự điền địa chỉ và số tiền để khách kiểm tra, xác nhận. Chuyển khoản thủ công vẫn có sẵn. Tiền đi thẳng vào ví nhận của người bán.

Sáu mạng: TRON, Ethereum, BNB Smart Chain, Polygon, Solana và Base. USDT hỗ trợ sáu mạng, USDC hỗ trợ năm mạng, tổng cộng mười một tổ hợp. TRON chỉ hỗ trợ USDT; USDT trên Base là Bridged USDT và USDC trên BNB là Binance-Peg USDC. Tùy chọn chỉ hiện khi dịch vụ, ví người bán và plugin đều bật tuyến đó.

## Cài đặt và cấu hình

Tải ZIP cài đặt v0.6.1 và tải lên trang plugin WordPress. Đặt tiền tệ cửa hàng là USD. Trong cùng tài khoản người bán BoltUtil, tạo LIVE API Key, cấu hình ví nhận và Webhook đang hoạt động, rồi lưu khóa và mạng trong plugin. Nâng cấp giữ nguyên đơn hàng và thông tin xác thực; cần bật USDC riêng.

**WordPress 7.0+ · PHP 7.4+ · WooCommerce 11.1+ · HTTPS · USD**

## Ngôn ngữ và thanh toán

WordPress quyết định ngôn ngữ giao diện. Nhãn do người bán tự viết không được dịch tự động. Có hạn mức miễn phí mỗi ngày; gói đăng ký không giới hạn số đơn mỗi ngày. Xem quyền lợi và phí hiện hành trên website. Trả đúng số tiền ở checkout; phí mạng được tính riêng. Quay lại cửa hàng không chứng minh đã trả tiền: plugin kiểm tra chữ ký và trạng thái trước khi hoàn tất đơn.

Xem demo miễn phí; mua sản phẩm nhỏ sử dụng tiền thật. Đây là bản phát hành GitHub sớm. Kiểm thử thanh toán thật và callback, cùng phê duyệt WordPress.org, vẫn chưa hoàn tất.

[Download v0.6.1](https://github.com/changye0413/boltutil-payments-for-woocommerce/releases/tag/v0.6.1) · [Live demo](https://shop-test.boltutil.com/) · [Integration guide](https://boltutil.com/woocommerce) · [Pricing](https://boltutil.com/pricing)

[Full setup and payment contract](../../README.md#configure-in-five-steps) · [Language guide](../I18N.md) · [Security](../../SECURITY.md)

![USDC checkout preview](../../wordpress-org-assets/screenshot-2.jpg)
