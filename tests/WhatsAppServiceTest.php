<?php

namespace Vendor\WhatsAppModule\Tests;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Vendor\WhatsAppModule\Services\WhatsAppService;
use Vendor\WhatsAppModule\Exceptions\WhatsAppException;
use Vendor\WhatsAppModule\Facades\WhatsApp;

/**
 * تست‌های کامل برای WhatsAppService
 */
class WhatsAppServiceTest extends TestCase
{
    /**
     * شماره تست برای استفاده در تست‌های واقعی
     * می‌توانید از متغیر محیطی استفاده کنید
     */
    protected $testNumber = null;
    protected $testSender = null;
    protected $testApiKey = null;
    protected $testUsername = null;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // دریافت مقادیر از متغیرهای محیطی برای تست واقعی
        $this->testNumber = env('TEST_WHATSAPP_NUMBER', '989123456789');
        $this->testSender = env('TEST_WHATSAPP_SENDER', '989123456789');
        $this->testApiKey = env('TEST_WHATSAPP_API_KEY', null);
        $this->testUsername = env('TEST_WHATSAPP_USERNAME', 'testuser');
    }

    /**
     * تست ارسال پیام متنی
     */
    public function test_send_message()
    {
        // Mock HTTP response
        Http::fake([
            'wa.ezlearn.store/send-message' => Http::response([
                'status' => true,
                'message' => 'Message sent successfully'
            ], 200)
        ]);

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');
        $service->setDefaultSender('989123456789');

        $result = $service->sendMessage('989123456789', 'Test message');

        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status']);
    }

    /**
     * تست ارسال پیام متنی با خطا
     */
    public function test_send_message_without_api_key()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionMessage('API Key تنظیم نشده است');

        $service = new WhatsAppService();
        $service->setApiKey('');
        $service->setDefaultSender('989123456789');

        $service->sendMessage('989123456789', 'Test message');
    }

    /**
     * تست اعتبارسنجی شماره بدون کد کشور
     */
    public function test_send_message_invalid_number()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionMessage('شماره گیرنده باید شامل کد کشور باشد');

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');
        $service->setDefaultSender('989123456789');

        $service->sendMessage('1234567', 'Test message'); // شماره بدون کد کشور
    }

    /**
     * تست ارسال رسانه
     */
    public function test_send_media()
    {
        Http::fake([
            'wa.ezlearn.store/send-media' => Http::response([
                'status' => true,
                'message' => 'Media sent successfully'
            ], 200)
        ]);

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');
        $service->setDefaultSender('989123456789');

        $result = $service->sendMedia(
            '989123456789',
            'image',
            'https://example.com/image.jpg',
            'Test caption'
        );

        $this->assertTrue($result['success']);
    }

    /**
     * تست ارسال رسانه با نوع نامعتبر
     */
    public function test_send_media_invalid_type()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionMessage('نوع رسانه نامعتبر است');

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');
        $service->setDefaultSender('989123456789');

        $service->sendMedia('989123456789', 'invalid_type', 'https://example.com/image.jpg');
    }

    /**
     * تست ارسال نظرسنجی
     */
    public function test_send_poll()
    {
        Http::fake([
            'wa.ezlearn.store/send-poll' => Http::response([
                'status' => true,
                'message' => 'Poll sent successfully'
            ], 200)
        ]);

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');
        $service->setDefaultSender('989123456789');

        $result = $service->sendPoll(
            '989123456789',
            'Test question?',
            ['Option 1', 'Option 2', 'Option 3'],
            false
        );

        $this->assertTrue($result['success']);
    }

    /**
     * تست ارسال نظرسنجی با کمتر از 2 گزینه
     */
    public function test_send_poll_insufficient_options()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionMessage('نظرسنجی باید حداقل 2 گزینه داشته باشد');

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');
        $service->setDefaultSender('989123456789');

        $service->sendPoll('989123456789', 'Test question?', ['Option 1'], false);
    }

    /**
     * تست ارسال استیکر
     */
    public function test_send_sticker()
    {
        Http::fake([
            'wa.ezlearn.store/send-sticker' => Http::response([
                'status' => true,
                'message' => 'Sticker sent successfully'
            ], 200)
        ]);

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');
        $service->setDefaultSender('989123456789');

        $result = $service->sendSticker(
            '989123456789',
            'https://example.com/sticker.webp'
        );

        $this->assertTrue($result['success']);
    }

    /**
     * تست ارسال دکمه
     */
    public function test_send_button()
    {
        Http::fake([
            'wa.ezlearn.store/send-button' => Http::response([
                'status' => true,
                'message' => 'Button sent successfully'
            ], 200)
        ]);

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');
        $service->setDefaultSender('989123456789');

        $result = $service->sendButton(
            '989123456789',
            'Test message',
            [
                ['type' => 'reply', 'displayText' => 'Reply'],
                ['type' => 'url', 'displayText' => 'Visit', 'url' => 'https://example.com']
            ],
            'https://example.com/image.jpg'
        );

        $this->assertTrue($result['success']);
    }

    /**
     * تست ارسال دکمه بدون تصویر
     */
    public function test_send_button_without_image()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionMessage('URL تصویر یا ویدیو الزامی است');

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');
        $service->setDefaultSender('989123456789');

        $service->sendButton(
            '989123456789',
            'Test message',
            [['type' => 'reply', 'displayText' => 'Reply']],
            '' // تصویر خالی
        );
    }

    /**
     * تست ارسال دکمه با بیش از 5 دکمه
     */
    public function test_send_button_too_many_buttons()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionMessage('حداکثر 5 دکمه مجاز است');

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');
        $service->setDefaultSender('989123456789');

        $buttons = [];
        for ($i = 1; $i <= 6; $i++) {
            $buttons[] = ['type' => 'reply', 'displayText' => "Button $i"];
        }

        $service->sendButton(
            '989123456789',
            'Test message',
            $buttons,
            'https://example.com/image.jpg'
        );
    }

    /**
     * تست تولید QR Code
     */
    public function test_generate_qr()
    {
        Http::fake([
            'wa.ezlearn.store/generate-qr' => Http::response([
                'status' => false,
                'qrcode' => 'data:image/png;base64,iVBORw0KGgo...',
                'message' => 'Please scann qrcode'
            ], 200)
        ]);

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');

        $result = $service->generateQR('989123456789', false);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('qrcode', $result);
    }

    /**
     * تست تولید QR Code - در حال پردازش
     */
    public function test_generate_qr_processing()
    {
        Http::fake([
            'wa.ezlearn.store/generate-qr' => Http::response([
                'status' => 'processing',
                'message' => 'Processing'
            ], 200)
        ]);

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');

        $result = $service->generateQR('989123456789', false);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['processing']);
    }

    /**
     * تست تولید QR Code - دستگاه متصل
     */
    public function test_generate_qr_already_connected()
    {
        Http::fake([
            'wa.ezlearn.store/generate-qr' => Http::response([
                'status' => false,
                'msg' => 'Device already connected!'
            ], 200)
        ]);

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');

        $result = $service->generateQR('989123456789', false);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['connected']);
    }

    /**
     * تست قطع اتصال دستگاه
     */
    public function test_disconnect_device()
    {
        Http::fake([
            'wa.ezlearn.store/logout-device' => Http::response([
                'status' => true,
                'message' => 'Device disconnected'
            ], 200)
        ]);

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');

        $result = $service->disconnectDevice('989123456789');

        $this->assertTrue($result['success']);
    }

    /**
     * تست دریافت اطلاعات کاربر
     */
    public function test_get_user_info()
    {
        Http::fake([
            'wa.ezlearn.store/info-user' => Http::response([
                'status' => true,
                'info' => [
                    'id' => 1,
                    'username' => 'testuser',
                    'email' => 'test@example.com',
                    'api_key' => 'test_api_key',
                    'level' => 'admin',
                    'status' => 'active'
                ]
            ], 200)
        ]);

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');

        $result = $service->getUserInfo('testuser');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals('testuser', $result['user']['username']);
    }

    /**
     * تست دریافت اطلاعات کاربر با username نامعتبر
     */
    public function test_get_user_info_invalid_username()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionMessage('نام کاربری نباید شامل نماد باشد');

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');

        $service->getUserInfo('test@user'); // شامل نماد @
    }

    /**
     * تست دریافت اطلاعات دستگاه
     */
    public function test_get_device_info()
    {
        Http::fake([
            'wa.ezlearn.store/info-device' => Http::response([
                'status' => true,
                'info' => [
                    [
                        'id' => 1,
                        'body' => '628122xxxxxx',
                        'status' => 'Connected',
                        'created_at' => '2024-08-16T11:07:27.000000Z'
                    ]
                ]
            ], 200)
        ]);

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');

        $result = $service->getDeviceInfo('989123456789');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('devices', $result);
    }

    /**
     * تست ایجاد دستگاه جدید
     */
    public function test_create_device()
    {
        Http::fake([
            'wa.ezlearn.store/create-device' => Http::response([
                'status' => true,
                'message' => 'Device created successfully'
            ], 200)
        ]);

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');

        $result = $service->createDevice(
            '989123456789',
            'https://example.com/webhook'
        );

        $this->assertTrue($result['success']);
    }

    /**
     * تست ایجاد دستگاه با sender کوتاه
     */
    public function test_create_device_short_sender()
    {
        $this->expectException(WhatsAppException::class);
        $this->expectExceptionMessage('شماره دستگاه باید حداقل 8 رقم داشته باشد');

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');

        $service->createDevice('1234567', 'https://example.com/webhook'); // کمتر از 8 رقم
    }

    /**
     * تست بررسی شماره
     */
    public function test_check_number()
    {
        Http::fake([
            'wa.ezlearn.store/check-number' => Http::response([
                'status' => true,
                'msg' => [
                    'exists' => true,
                    'jid' => '201111347197@s.whatsapp.net'
                ]
            ], 200)
        ]);

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');

        $result = $service->checkNumber('989123456789', '989123456780');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('exists', $result);
        $this->assertTrue($result['exists']);
    }

    /**
     * تست بررسی شماره - شماره وجود ندارد
     */
    public function test_check_number_not_exists()
    {
        Http::fake([
            'wa.ezlearn.store/check-number' => Http::response([
                'status' => true,
                'msg' => [
                    'exists' => false
                ]
            ], 200)
        ]);

        $service = new WhatsAppService();
        $service->setApiKey('test_api_key');

        $result = $service->checkNumber('989123456789', '989123456780');

        $this->assertTrue($result['success']);
        $this->assertFalse($result['exists']);
    }

    /**
     * تست Facade
     */
    public function test_facade_works()
    {
        Http::fake([
            'wa.ezlearn.store/send-message' => Http::response([
                'status' => true,
                'message' => 'Message sent'
            ], 200)
        ]);

        Config::set('whatsapp.api_key', 'test_api_key');
        Config::set('whatsapp.default_sender', '989123456789');

        $result = WhatsApp::sendMessage('989123456789', 'Test message');

        $this->assertTrue($result['success']);
    }

    /**
     * تست تنظیمات موقت
     */
    public function test_setters()
    {
        $service = new WhatsAppService();

        $service->setBaseUrl('https://custom.url');
        $service->setApiKey('custom_key');
        $service->setDefaultSender('62888123456');

        $this->assertEquals('https://custom.url', $service->getBaseUrl());
        $this->assertEquals('custom_key', $service->getApiKey());
    }
}

