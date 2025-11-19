<?php

namespace Vendor\WhatsAppModule\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Vendor\WhatsAppModule\WhatsAppModuleServiceProvider;

/**
 * کلاس پایه برای تست‌ها
 */
abstract class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // تنظیمات پیش‌فرض برای تست
        $this->app['config']->set('whatsapp.base_url', env('WHATSAPP_BASE_URL', 'https://wa.ezlearn.store'));
        $this->app['config']->set('whatsapp.api_key', env('WHATSAPP_API_KEY', 'test_api_key'));
        $this->app['config']->set('whatsapp.default_sender', env('WHATSAPP_DEFAULT_SENDER', '989123456789'));
        $this->app['config']->set('whatsapp.timeout', 30);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            WhatsAppModuleServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'WhatsApp' => \Vendor\WhatsAppModule\Facades\WhatsApp::class,
        ];
    }
}

