<?php

namespace Vendor\WhatsAppModule\Tests;

use Vendor\WhatsAppModule\Services\WhatsAppService;
use Vendor\WhatsAppModule\Facades\WhatsApp;

/**
 * تست‌های واقعی با API واقعی
 * 
 * برای اجرای این تست‌ها، باید متغیرهای محیطی زیر را تنظیم کنید:
 * - TEST_WHATSAPP_API_KEY: API Key واقعی شما
 * - TEST_WHATSAPP_SENDER: شماره فرستنده واقعی (با کد کشور)
 * - TEST_WHATSAPP_NUMBER: شماره گیرنده برای تست (با کد کشور)
 * - TEST_WHATSAPP_USERNAME: نام کاربری برای تست
 * 
 * توجه: این تست‌ها فقط در صورت تنظیم متغیرهای محیطی اجرا می‌شوند
 */
class WhatsAppServiceRealTest extends TestCase
{
    protected $testNumber = null;
    protected $testSender = null;
    protected $testApiKey = null;
    protected $testUsername = null;
    protected $skipRealTests = true;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // دریافت مقادیر از متغیرهای محیطی
        $this->testApiKey = env('TEST_WHATSAPP_API_KEY');
        $this->testSender = env('TEST_WHATSAPP_SENDER');
        $this->testNumber = env('TEST_WHATSAPP_NUMBER');
        $this->testUsername = env('TEST_WHATSAPP_USERNAME');

        // اگر API Key تنظیم نشده باشد، تست‌ها را skip می‌کنیم
        if (empty($this->testApiKey) || empty($this->testSender)) {
            $this->skipRealTests = true;
            $this->markTestSkipped('متغیرهای محیطی TEST_WHATSAPP_API_KEY و TEST_WHATSAPP_SENDER تنظیم نشده‌اند');
        } else {
            $this->skipRealTests = false;
        }
    }

    /**
     * تست واقعی: بررسی شماره
     */
    public function test_real_check_number()
    {
        if ($this->skipRealTests) {
            $this->markTestSkipped('تست واقعی نیاز به تنظیم متغیرهای محیطی دارد');
        }

        $service = new WhatsAppService();
        $service->setApiKey($this->testApiKey);

        $result = $service->checkNumber($this->testSender, $this->testNumber ?? $this->testSender);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        
        if ($result['success']) {
            $this->assertArrayHasKey('exists', $result);
            echo "\n✓ شماره " . ($result['exists'] ? 'دارای' : 'فاقد') . " واتساپ است\n";
        }
    }

    /**
     * تست واقعی: دریافت اطلاعات کاربر
     */
    public function test_real_get_user_info()
    {
        if ($this->skipRealTests || empty($this->testUsername)) {
            $this->markTestSkipped('تست واقعی نیاز به TEST_WHATSAPP_USERNAME دارد');
        }

        $service = new WhatsAppService();
        $service->setApiKey($this->testApiKey);

        $result = $service->getUserInfo($this->testUsername);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        
        if ($result['success']) {
            $this->assertArrayHasKey('user', $result);
            echo "\n✓ اطلاعات کاربر دریافت شد:\n";
            echo "  - Username: " . ($result['user']['username'] ?? 'N/A') . "\n";
            echo "  - Level: " . ($result['user']['level'] ?? 'N/A') . "\n";
            echo "  - Status: " . ($result['user']['status'] ?? 'N/A') . "\n";
        }
    }

    /**
     * تست واقعی: دریافت اطلاعات دستگاه
     */
    public function test_real_get_device_info()
    {
        if ($this->skipRealTests) {
            $this->markTestSkipped('تست واقعی نیاز به تنظیم متغیرهای محیطی دارد');
        }

        $service = new WhatsAppService();
        $service->setApiKey($this->testApiKey);

        $result = $service->getDeviceInfo($this->testSender);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        
        if ($result['success']) {
            $this->assertArrayHasKey('devices', $result);
            echo "\n✓ اطلاعات دستگاه دریافت شد\n";
            echo "  - تعداد دستگاه‌ها: " . count($result['devices']) . "\n";
        }
    }

    /**
     * تست واقعی: ارسال پیام متنی (فقط اگر شماره تست تنظیم شده باشد)
     */
    public function test_real_send_message()
    {
        if ($this->skipRealTests || empty($this->testNumber)) {
            $this->markTestSkipped('تست واقعی نیاز به TEST_WHATSAPP_NUMBER دارد');
        }

        // ابتدا بررسی می‌کنیم شماره واتساپ دارد یا نه
        $service = new WhatsAppService();
        $service->setApiKey($this->testApiKey);

        $checkResult = $service->checkNumber($this->testSender, $this->testNumber);
        
        if (!$checkResult['success'] || !($checkResult['exists'] ?? false)) {
            $this->markTestSkipped('شماره مقصد واتساپ ندارد یا بررسی ناموفق بود');
        }

        // ارسال پیام
        $service->setDefaultSender($this->testSender);
        $result = $service->sendMessage($this->testNumber, 'این یک پیام تست از Unit Test است');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        
        if ($result['success']) {
            echo "\n✓ پیام با موفقیت ارسال شد\n";
        } else {
            echo "\n✗ خطا در ارسال پیام: " . ($result['error'] ?? 'Unknown error') . "\n";
        }
    }
}

