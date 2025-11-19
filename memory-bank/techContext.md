# Tech Context

## تکنولوژی‌های استفاده شده
- **Laravel**: Framework اصلی (نسخه 10.x و 11.x)
- **PHP**: >= 8.1
- **Composer**: مدیریت وابستگی‌ها
- **Illuminate/Support**: برای Facade و Service Provider
- **Illuminate/Http**: برای درخواست‌های HTTP

## تنظیمات توسعه
- ساختار Package Laravel استاندارد
- Service Provider (WhatsAppModuleServiceProvider)
- Facade (WhatsApp)
- Config File (config/whatsapp.php)
- Routes (routes/api.php)
- Controller (WhatsAppController)

## محدودیت‌های فنی
- نیاز به Laravel 10.x یا 11.x
- نیاز به PHP 8.1 یا بالاتر
- سرویس واتساپ: wa.ezlearn.store

## وابستگی‌ها
- `illuminate/support`: ^10.0|^11.0
- `illuminate/http`: ^10.0|^11.0
- `orchestra/testbench`: ^8.0|^9.0 (برای تست)
- `phpunit/phpunit`: ^10.0 (برای تست)

## متغیرهای محیطی
- `WHATSAPP_BASE_URL`: URL پایه API (پیش‌فرض: https://wa.ezlearn.store)
- `WHATSAPP_API_KEY`: API Key برای احراز هویت
- `WHATSAPP_DEFAULT_SENDER`: شماره فرستنده پیش‌فرض
- `WHATSAPP_TIMEOUT`: زمان انتظار برای درخواست‌ها (پیش‌فرض: 30 ثانیه)

