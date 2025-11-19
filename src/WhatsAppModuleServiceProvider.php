<?php

namespace Vendor\WhatsAppModule;

use Illuminate\Support\ServiceProvider;
use Vendor\WhatsAppModule\Services\WhatsAppService;

/**
 * Service Provider برای ماژول واتساپ
 */
class WhatsAppModuleServiceProvider extends ServiceProvider
{
    /**
     * ثبت سرویس‌ها
     *
     * @return void
     */
    public function register(): void
    {
        // ثبت فایل تنظیمات
        $this->mergeConfigFrom(
            __DIR__ . '/../config/whatsapp.php',
            'whatsapp'
        );

        // ثبت سرویس واتساپ به عنوان Singleton
        $this->app->singleton('whatsapp', function ($app) {
            return new WhatsAppService();
        });

        // ثبت alias برای دسترسی مستقیم
        $this->app->alias('whatsapp', WhatsAppService::class);
    }

    /**
     * بوت کردن سرویس‌ها
     *
     * @return void
     */
    public function boot(): void
    {
        // انتشار فایل تنظیمات
        $this->publishes([
            __DIR__ . '/../config/whatsapp.php' => config_path('whatsapp.php'),
        ], 'whatsapp-config');

        // بارگذاری routes در صورت وجود
        if (file_exists(__DIR__ . '/../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }
    }
}

