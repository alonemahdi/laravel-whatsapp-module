# System Patterns

## معماری سیستم
- ساختار Package Laravel استاندارد
- Service Provider برای ثبت سرویس‌ها
- Facade برای دسترسی آسان
- Config-based Configuration (URL و API Key سراسری)

## تصمیمات فنی کلیدی
- استفاده از ساختار Package Laravel
- Service Provider برای ثبت سرویس‌ها به صورت Singleton
- Facade برای دسترسی آسان (WhatsApp::sendMessage())
- مدیریت URL و API Key از طریق Config (قابل تنظیم در .env)
- استفاده از Illuminate\Support\Facades\Http برای درخواست‌های HTTP

## الگوهای طراحی
- Service Pattern: WhatsAppService برای مدیریت اتصال و ارسال پیام
- Facade Pattern: برای دسترسی آسان به سرویس
- Singleton Pattern: سرویس به صورت Singleton ثبت می‌شود

## روابط کامپوننت‌ها
```
WhatsAppModuleServiceProvider
    └──> WhatsAppService (Singleton)
            └──> Config (برای URL و API Key)
            └──> Http Facade (برای درخواست‌ها)
    
WhatsApp Facade
    └──> WhatsAppService
    
WhatsAppController
    └──> WhatsApp Facade
```

