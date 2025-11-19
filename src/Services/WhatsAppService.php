<?php

namespace Vendor\WhatsAppModule\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Vendor\WhatsAppModule\Exceptions\WhatsAppException;

/**
 * کلاس سرویس واتساپ برای ارسال و مدیریت پیام‌ها
 */
class WhatsAppService
{
    /**
     * URL پایه API
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * API Key برای احراز هویت
     *
     * @var string
     */
    protected $apiKey;

    /**
     * شماره فرستنده پیش‌فرض
     *
     * @var string
     */
    protected $defaultSender;

    /**
     * زمان انتظار برای درخواست‌ها
     *
     * @var int
     */
    protected $timeout;

    /**
     * سازنده کلاس
     */
    public function __construct()
    {
        $this->baseUrl = Config::get('whatsapp.base_url');
        $this->apiKey = Config::get('whatsapp.api_key');
        $this->defaultSender = Config::get('whatsapp.default_sender');
        $this->timeout = Config::get('whatsapp.timeout', 30);
    }

    /**
     * تنظیم URL پایه
     *
     * @param string $url
     * @return $this
     */
    public function setBaseUrl(string $url): self
    {
        $this->baseUrl = $url;
        return $this;
    }

    /**
     * تنظیم API Key
     *
     * @param string $apiKey
     * @return $this
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * تنظیم شماره فرستنده پیش‌فرض
     *
     * @param string $sender
     * @return $this
     */
    public function setDefaultSender(string $sender): self
    {
        $this->defaultSender = $sender;
        return $this;
    }

    /**
     * بررسی و اعتبارسنجی شماره با کد کشور
     *
     * @param string $number شماره برای بررسی
     * @param string $fieldName نام فیلد برای پیام خطا
     * @return string شماره اعتبارسنجی شده
     * @throws WhatsAppException
     */
    protected function validatePhoneNumber(string $number, string $fieldName = 'شماره'): string
    {
        // حذف فاصله‌ها و کاراکترهای اضافی
        $number = preg_replace('/[\s\-\(\)]/', '', $number);

        // بررسی اینکه شماره با کد کشور شروع می‌شود (شروع با عدد)
        if (!preg_match('/^\d{8,15}$/', $number)) {
            throw new WhatsAppException("{$fieldName} باید شامل کد کشور باشد و فقط شامل اعداد باشد (مثال: 989123456789 یا 62888123456)");
        }

        // بررسی حداقل طول (حداقل 8 رقم برای شماره با کد کشور)
        if (strlen($number) < 8) {
            throw new WhatsAppException("{$fieldName} باید حداقل 8 رقم داشته باشد و شامل کد کشور باشد");
        }

        return $number;
    }

    /**
     * ارسال پیام متنی
     *
     * @param string $number شماره گیرنده (باید با کد کشور باشد، مثال: 989123456789 یا 62888123456)
     * @param string $message متن پیام
     * @param string|null $sender شماره فرستنده (باید با کد کشور باشد - اختیاری - در صورت عدم ارسال از پیش‌فرض استفاده می‌شود)
     * @return array
     * @throws WhatsAppException
     */
    public function sendMessage(string $number, string $message, ?string $sender = null): array
    {
        // بررسی وجود API Key
        if (empty($this->apiKey)) {
            throw new WhatsAppException('API Key تنظیم نشده است. لطفاً WHATSAPP_API_KEY را در فایل .env تنظیم کنید.');
        }

        // استفاده از فرستنده پیش‌فرض در صورت عدم ارسال
        $sender = $sender ?? $this->defaultSender;

        if (empty($sender)) {
            throw new WhatsAppException('شماره فرستنده تنظیم نشده است. لطفاً WHATSAPP_DEFAULT_SENDER را در فایل .env تنظیم کنید.');
        }

        // اعتبارسنجی شماره‌ها با کد کشور
        $number = $this->validatePhoneNumber($number, 'شماره گیرنده');
        $sender = $this->validatePhoneNumber($sender, 'شماره فرستنده');

        // آماده‌سازی داده‌های درخواست
        $data = [
            'api_key' => $this->apiKey,
            'sender' => $sender,
            'number' => $number,
            'message' => $message,
        ];

        // ارسال درخواست POST
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/send-message', $data);

            // بررسی موفقیت درخواست
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            // در صورت خطا
            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            throw new WhatsAppException('خطا در ارسال پیام: ' . $e->getMessage());
        }
    }

    /**
     * ارسال رسانه (عکس، ویدیو، صدا، سند)
     *
     * @param string $number شماره گیرنده (باید با کد کشور باشد، مثال: 989123456789 یا 62888123456)
     * @param string $mediaType نوع رسانه (image, video, audio, document)
     * @param string $url آدرس مستقیم رسانه (باید لینک مستقیم باشد، نه از Google Drive یا cloud storage)
     * @param string|null $caption متن توضیحات (اختیاری)
     * @param string|null $sender شماره فرستنده (باید با کد کشور باشد - اختیاری - در صورت عدم ارسال از پیش‌فرض استفاده می‌شود)
     * @return array
     * @throws WhatsAppException
     */
    public function sendMedia(
        string $number,
        string $mediaType,
        string $url,
        ?string $caption = null,
        ?string $sender = null
    ): array {
        // بررسی وجود API Key
        if (empty($this->apiKey)) {
            throw new WhatsAppException('API Key تنظیم نشده است. لطفاً WHATSAPP_API_KEY را در فایل .env تنظیم کنید.');
        }

        // استفاده از فرستنده پیش‌فرض در صورت عدم ارسال
        $sender = $sender ?? $this->defaultSender;

        if (empty($sender)) {
            throw new WhatsAppException('شماره فرستنده تنظیم نشده است. لطفاً WHATSAPP_DEFAULT_SENDER را در فایل .env تنظیم کنید.');
        }

        // اعتبارسنجی شماره‌ها با کد کشور
        $number = $this->validatePhoneNumber($number, 'شماره گیرنده');
        $sender = $this->validatePhoneNumber($sender, 'شماره فرستنده');

        // اعتبارسنجی نوع رسانه
        $allowedTypes = ['image', 'video', 'audio', 'document'];
        $mediaType = strtolower($mediaType);
        
        if (!in_array($mediaType, $allowedTypes)) {
            throw new WhatsAppException("نوع رسانه نامعتبر است. انواع مجاز: " . implode(', ', $allowedTypes));
        }

        // بررسی URL
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new WhatsAppException('URL رسانه نامعتبر است. لطفاً یک لینک مستقیم و معتبر وارد کنید.');
        }

        // آماده‌سازی داده‌های درخواست
        $data = [
            'api_key' => $this->apiKey,
            'sender' => $sender,
            'number' => $number,
            'media_type' => $mediaType,
            'url' => $url,
        ];

        // افزودن caption در صورت وجود
        if (!empty($caption)) {
            $data['caption'] = $caption;
        }

        // ارسال درخواست POST
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/send-media', $data);

            // بررسی موفقیت درخواست
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            // در صورت خطا
            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            throw new WhatsAppException('خطا در ارسال رسانه: ' . $e->getMessage());
        }
    }

    /**
     * ارسال نظرسنجی (Poll)
     *
     * @param string $number شماره گیرنده (باید با کد کشور باشد، مثال: 989123456789)
     * @param string $name نام یا سوال نظرسنجی
     * @param array $options آرایه گزینه‌های نظرسنجی (مثال: ['گزینه 1', 'گزینه 2', 'گزینه 3'])
     * @param bool $countable آیا فقط یک گزینه قابل انتخاب است؟ (true = فقط یک گزینه، false = چند گزینه)
     * @param string|null $sender شماره فرستنده (باید با کد کشور باشد - اختیاری)
     * @return array
     * @throws WhatsAppException
     */
    public function sendPoll(
        string $number,
        string $name,
        array $options,
        bool $countable = false,
        ?string $sender = null
    ): array {
        // بررسی وجود API Key
        if (empty($this->apiKey)) {
            throw new WhatsAppException('API Key تنظیم نشده است. لطفاً WHATSAPP_API_KEY را در فایل .env تنظیم کنید.');
        }

        // استفاده از فرستنده پیش‌فرض در صورت عدم ارسال
        $sender = $sender ?? $this->defaultSender;

        if (empty($sender)) {
            throw new WhatsAppException('شماره فرستنده تنظیم نشده است. لطفاً WHATSAPP_DEFAULT_SENDER را در فایل .env تنظیم کنید.');
        }

        // اعتبارسنجی شماره‌ها با کد کشور
        $number = $this->validatePhoneNumber($number, 'شماره گیرنده');
        $sender = $this->validatePhoneNumber($sender, 'شماره فرستنده');

        // اعتبارسنجی نام نظرسنجی
        if (empty(trim($name))) {
            throw new WhatsAppException('نام یا سوال نظرسنجی نمی‌تواند خالی باشد.');
        }

        // اعتبارسنجی گزینه‌ها
        if (empty($options) || !is_array($options)) {
            throw new WhatsAppException('گزینه‌های نظرسنجی باید یک آرایه غیرخالی باشد.');
        }

        // بررسی حداقل 2 گزینه
        if (count($options) < 2) {
            throw new WhatsAppException('نظرسنجی باید حداقل 2 گزینه داشته باشد.');
        }

        // تبدیل آرایه به آرایه ساده (حذف کلیدهای غیرعددی)
        $options = array_values($options);

        // آماده‌سازی داده‌های درخواست
        $data = [
            'api_key' => $this->apiKey,
            'sender' => $sender,
            'number' => $number,
            'name' => $name,
            'option' => $options,
            'countable' => $countable ? '1' : '0',
        ];

        // ارسال درخواست POST
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/send-poll', $data);

            // بررسی موفقیت درخواست
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            // در صورت خطا
            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            throw new WhatsAppException('خطا در ارسال نظرسنجی: ' . $e->getMessage());
        }
    }

    /**
     * ارسال استیکر
     *
     * @param string $number شماره گیرنده (باید با کد کشور باشد، مثال: 989123456789)
     * @param string $url آدرس مستقیم استیکر (باید لینک مستقیم باشد، نه از Google Drive یا cloud storage)
     * @param string|null $sender شماره فرستنده (باید با کد کشور باشد - اختیاری)
     * @return array
     * @throws WhatsAppException
     */
    public function sendSticker(
        string $number,
        string $url,
        ?string $sender = null
    ): array {
        // بررسی وجود API Key
        if (empty($this->apiKey)) {
            throw new WhatsAppException('API Key تنظیم نشده است. لطفاً WHATSAPP_API_KEY را در فایل .env تنظیم کنید.');
        }

        // استفاده از فرستنده پیش‌فرض در صورت عدم ارسال
        $sender = $sender ?? $this->defaultSender;

        if (empty($sender)) {
            throw new WhatsAppException('شماره فرستنده تنظیم نشده است. لطفاً WHATSAPP_DEFAULT_SENDER را در فایل .env تنظیم کنید.');
        }

        // اعتبارسنجی شماره‌ها با کد کشور
        $number = $this->validatePhoneNumber($number, 'شماره گیرنده');
        $sender = $this->validatePhoneNumber($sender, 'شماره فرستنده');

        // بررسی URL
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new WhatsAppException('URL استیکر نامعتبر است. لطفاً یک لینک مستقیم و معتبر وارد کنید.');
        }

        // آماده‌سازی داده‌های درخواست
        $data = [
            'api_key' => $this->apiKey,
            'sender' => $sender,
            'number' => $number,
            'url' => $url,
        ];

        // ارسال درخواست POST
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/send-sticker', $data);

            // بررسی موفقیت درخواست
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            // در صورت خطا
            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            throw new WhatsAppException('خطا در ارسال استیکر: ' . $e->getMessage());
        }
    }

    /**
     * ارسال پیام با دکمه‌های تعاملی
     *
     * @param string $number شماره گیرنده (باید با کد کشور باشد، مثال: 989123456789)
     * @param string $message متن پیام
     * @param array $buttons آرایه دکمه‌ها (حداکثر 5 دکمه)
     * @param string $imageUrl آدرس تصویر یا ویدیو (الزامی برای اطمینان از ارسال)
     * @param string|null $footer متن footer (اختیاری)
     * @param string|null $sender شماره فرستنده (باید با کد کشور باشد - اختیاری)
     * @return array
     * @throws WhatsAppException
     */
    public function sendButton(
        string $number,
        string $message,
        array $buttons,
        string $imageUrl,
        ?string $footer = null,
        ?string $sender = null
    ): array {
        // بررسی وجود API Key
        if (empty($this->apiKey)) {
            throw new WhatsAppException('API Key تنظیم نشده است. لطفاً WHATSAPP_API_KEY را در فایل .env تنظیم کنید.');
        }

        // استفاده از فرستنده پیش‌فرض در صورت عدم ارسال
        $sender = $sender ?? $this->defaultSender;

        if (empty($sender)) {
            throw new WhatsAppException('شماره فرستنده تنظیم نشده است. لطفاً WHATSAPP_DEFAULT_SENDER را در فایل .env تنظیم کنید.');
        }

        // اعتبارسنجی شماره‌ها با کد کشور
        $number = $this->validatePhoneNumber($number, 'شماره گیرنده');
        $sender = $this->validatePhoneNumber($sender, 'شماره فرستنده');

        // اعتبارسنجی متن پیام
        if (empty(trim($message))) {
            throw new WhatsAppException('متن پیام نمی‌تواند خالی باشد.');
        }

        // اعتبارسنجی دکمه‌ها
        if (empty($buttons) || !is_array($buttons)) {
            throw new WhatsAppException('دکمه‌ها باید یک آرایه غیرخالی باشد.');
        }

        // بررسی حداکثر 5 دکمه
        if (count($buttons) > 5) {
            throw new WhatsAppException('حداکثر 5 دکمه مجاز است.');
        }

        // اعتبارسنجی و فرمت‌دهی دکمه‌ها
        $validatedButtons = [];
        $allowedTypes = ['reply', 'call', 'url', 'copy'];

        foreach ($buttons as $index => $button) {
            if (!is_array($button)) {
                throw new WhatsAppException("دکمه شماره " . ($index + 1) . " باید یک آرایه باشد.");
            }

            // بررسی وجود type
            if (empty($button['type']) || !in_array($button['type'], $allowedTypes)) {
                throw new WhatsAppException("نوع دکمه شماره " . ($index + 1) . " نامعتبر است. انواع مجاز: " . implode(', ', $allowedTypes));
            }

            // بررسی وجود displayText
            if (empty($button['displayText'])) {
                throw new WhatsAppException("متن نمایشی دکمه شماره " . ($index + 1) . " نمی‌تواند خالی باشد.");
            }

            $validatedButton = [
                'type' => $button['type'],
                'displayText' => $button['displayText'],
            ];

            // بررسی فیلدهای اضافی بر اساس نوع دکمه
            switch ($button['type']) {
                case 'call':
                    if (empty($button['phoneNumber'])) {
                        throw new WhatsAppException("دکمه call شماره " . ($index + 1) . " باید phoneNumber داشته باشد.");
                    }
                    $validatedButton['phoneNumber'] = $button['phoneNumber'];
                    break;

                case 'url':
                    if (empty($button['url']) || !filter_var($button['url'], FILTER_VALIDATE_URL)) {
                        throw new WhatsAppException("دکمه url شماره " . ($index + 1) . " باید یک URL معتبر داشته باشد.");
                    }
                    $validatedButton['url'] = $button['url'];
                    break;

                case 'copy':
                    if (empty($button['copyCode'])) {
                        throw new WhatsAppException("دکمه copy شماره " . ($index + 1) . " باید copyCode داشته باشد.");
                    }
                    $validatedButton['copyCode'] = $button['copyCode'];
                    break;
            }

            $validatedButtons[] = $validatedButton;
        }

        // بررسی URL تصویر (الزامی)
        if (empty($imageUrl) || !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            throw new WhatsAppException('URL تصویر یا ویدیو الزامی است و باید یک لینک مستقیم و معتبر باشد.');
        }

        // آماده‌سازی داده‌های درخواست
        $data = [
            'api_key' => $this->apiKey,
            'sender' => $sender,
            'number' => $number,
            'message' => $message,
            'button' => $validatedButtons,
            'url' => $imageUrl,
        ];

        // افزودن footer در صورت وجود
        if (!empty($footer)) {
            $data['footer'] = $footer;
        }

        // ارسال درخواست POST
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/send-button', $data);

            // بررسی موفقیت درخواست
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            // در صورت خطا
            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            throw new WhatsAppException('خطا در ارسال دکمه: ' . $e->getMessage());
        }
    }

    /**
     * تولید QR Code برای اتصال دستگاه به واتساپ
     *
     * @param string $device شماره دستگاه (باید با کد کشور باشد، مثال: 989123456789)
     * @param bool $force اگر true باشد، در صورت عدم وجود دستگاه، ایجاد می‌شود
     * @return array
     * @throws WhatsAppException
     */
    public function generateQR(string $device, bool $force = false): array
    {
        // بررسی وجود API Key
        if (empty($this->apiKey)) {
            throw new WhatsAppException('API Key تنظیم نشده است. لطفاً WHATSAPP_API_KEY را در فایل .env تنظیم کنید.');
        }

        // اعتبارسنجی شماره دستگاه با کد کشور
        $device = $this->validatePhoneNumber($device, 'شماره دستگاه');

        // آماده‌سازی داده‌های درخواست
        $data = [
            'api_key' => $this->apiKey,
            'device' => $device,
            'force' => $force,
        ];

        // ارسال درخواست POST
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/generate-qr', $data);

            // بررسی موفقیت درخواست
            if ($response->successful()) {
                $responseData = $response->json();

                // بررسی وضعیت پاسخ
                if (isset($responseData['status'])) {
                    // در حال پردازش
                    if ($responseData['status'] === 'processing') {
                        return [
                            'success' => true,
                            'processing' => true,
                            'message' => $responseData['message'] ?? 'در حال پردازش...',
                            'data' => $responseData,
                            'status' => $response->status(),
                        ];
                    }

                    // دستگاه قبلاً متصل است
                    if ($responseData['status'] === false && isset($responseData['msg']) && 
                        strpos(strtolower($responseData['msg']), 'already connected') !== false) {
                        return [
                            'success' => true,
                            'connected' => true,
                            'message' => $responseData['msg'],
                            'data' => $responseData,
                            'status' => $response->status(),
                        ];
                    }

                    // QR Code آماده است
                    if ($responseData['status'] === false && isset($responseData['qrcode'])) {
                        return [
                            'success' => true,
                            'qrcode' => $responseData['qrcode'],
                            'message' => $responseData['message'] ?? 'لطفاً QR Code را اسکن کنید',
                            'data' => $responseData,
                            'status' => $response->status(),
                        ];
                    }

                    // خطا
                    if ($responseData['status'] === false && isset($responseData['msg'])) {
                        return [
                            'success' => false,
                            'message' => $responseData['msg'],
                            'errors' => $responseData['errors'] ?? [],
                            'data' => $responseData,
                            'status' => $response->status(),
                        ];
                    }
                }

                // پاسخ ناشناخته
                return [
                    'success' => true,
                    'data' => $responseData,
                    'status' => $response->status(),
                ];
            }

            // در صورت خطای HTTP
            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            throw new WhatsAppException('خطا در تولید QR Code: ' . $e->getMessage());
        }
    }

    /**
     * قطع اتصال دستگاه از واتساپ
     *
     * @param string $sender شماره دستگاه برای قطع اتصال (باید با کد کشور باشد، مثال: 989123456789)
     * @return array
     * @throws WhatsAppException
     */
    public function disconnectDevice(string $sender): array
    {
        // بررسی وجود API Key
        if (empty($this->apiKey)) {
            throw new WhatsAppException('API Key تنظیم نشده است. لطفاً WHATSAPP_API_KEY را در فایل .env تنظیم کنید.');
        }

        // اعتبارسنجی شماره دستگاه با کد کشور
        $sender = $this->validatePhoneNumber($sender, 'شماره دستگاه');

        // آماده‌سازی داده‌های درخواست
        $data = [
            'api_key' => $this->apiKey,
            'sender' => $sender,
        ];

        // ارسال درخواست POST
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/logout-device', $data);

            // بررسی موفقیت درخواست
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            // در صورت خطا
            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            throw new WhatsAppException('خطا در قطع اتصال دستگاه: ' . $e->getMessage());
        }
    }

    /**
     * دریافت اطلاعات کاربر بر اساس API Key و Username
     *
     * @param string $username نام کاربری (نباید شامل نماد باشد)
     * @param string|null $apiKey API Key (اختیاری - در صورت عدم ارسال از config استفاده می‌شود)
     * @return array
     * @throws WhatsAppException
     */
    public function getUserInfo(string $username, ?string $apiKey = null): array
    {
        // استفاده از API Key از config در صورت عدم ارسال
        $apiKey = $apiKey ?? $this->apiKey;

        if (empty($apiKey)) {
            throw new WhatsAppException('API Key تنظیم نشده است. لطفاً WHATSAPP_API_KEY را در فایل .env تنظیم کنید یا به عنوان پارامتر ارسال کنید.');
        }

        // اعتبارسنجی username (نباید شامل نماد باشد)
        if (empty(trim($username))) {
            throw new WhatsAppException('نام کاربری نمی‌تواند خالی باشد.');
        }

        // بررسی وجود نماد در username
        if (preg_match('/[^a-zA-Z0-9_]/', $username)) {
            throw new WhatsAppException('نام کاربری نباید شامل نماد باشد. فقط حروف، اعداد و زیرخط مجاز است.');
        }

        // آماده‌سازی داده‌های درخواست
        $data = [
            'api_key' => $apiKey,
            'username' => $username,
        ];

        // ارسال درخواست POST
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/info-user', $data);

            // بررسی موفقیت درخواست
            if ($response->successful()) {
                $responseData = $response->json();

                // بررسی وضعیت پاسخ
                if (isset($responseData['status']) && $responseData['status'] === true && isset($responseData['info'])) {
                    return [
                        'success' => true,
                        'user' => $responseData['info'],
                        'data' => $responseData,
                        'status' => $response->status(),
                    ];
                }

                // در صورت خطا
                return [
                    'success' => false,
                    'error' => $responseData['message'] ?? 'خطا در دریافت اطلاعات کاربر',
                    'data' => $responseData,
                    'status' => $response->status(),
                ];
            }

            // در صورت خطای HTTP
            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            throw new WhatsAppException('خطا در دریافت اطلاعات کاربر: ' . $e->getMessage());
        }
    }

    /**
     * اطلاعات دستگاه متصل را بر اساس شماره دریافت می‌کند
     *
     * @param string $number شماره دستگاه (باید با کد کشور باشد)
     * @param string|null $apiKey API Key (اختیاری - در صورت عدم ارسال از config استفاده می‌شود)
     * @return array
     * @throws WhatsAppException
     */
    public function getDeviceInfo(string $number, ?string $apiKey = null): array
    {
        $apiKey = $apiKey ?? $this->apiKey;

        if (empty($apiKey)) {
            throw new WhatsAppException('API Key تنظیم نشده است. لطفاً WHATSAPP_API_KEY را در فایل .env تنظیم کنید یا به عنوان پارامتر ارسال کنید.');
        }

        $number = $this->validatePhoneNumber($number, 'شماره دستگاه');

        $data = [
            'api_key' => $apiKey,
            'number' => $number,
        ];

        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/info-device', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            throw new WhatsAppException('خطا در دریافت اطلاعات دستگاه: ' . $e->getMessage());
        }
    }

    /**
     * ایجاد دستگاه جدید برای ارسال پیام‌ها
     *
     * @param string $sender شماره ارسال‌کننده (باید با کد کشور باشد و حداقل 8 رقم)
     * @param string|null $urlWebhook آدرس وبهوک (اختیاری)
     * @param string|null $apiKey API Key (اختیاری - در صورت عدم ارسال از config استفاده می‌شود)
     * @return array
     * @throws WhatsAppException
     */
    public function createDevice(string $sender, ?string $urlWebhook = null, ?string $apiKey = null): array
    {
        $apiKey = $apiKey ?? $this->apiKey;

        if (empty($apiKey)) {
            throw new WhatsAppException('API Key تنظیم نشده است. لطفاً WHATSAPP_API_KEY را در فایل .env تنظیم کنید یا به عنوان پارامتر ارسال کنید.');
        }

        $sender = $this->validatePhoneNumber($sender, 'شماره دستگاه');

        if (strlen($sender) < 8) {
            throw new WhatsAppException('شماره دستگاه باید حداقل 8 رقم داشته باشد.');
        }

        $data = [
            'api_key' => $apiKey,
            'sender' => $sender,
        ];

        if (!empty($urlWebhook)) {
            if (!filter_var($urlWebhook, FILTER_VALIDATE_URL)) {
                throw new WhatsAppException('آدرس وبهوک نامعتبر است.');
            }
            $data['urlwebhook'] = $urlWebhook;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/create-device', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            throw new WhatsAppException('خطا در ایجاد دستگاه جدید: ' . $e->getMessage());
        }
    }

    /**
     * بررسی می‌کند که آیا شماره موردنظر در واتساپ فعال است یا خیر
     *
     * @param string $sender شماره دستگاه شما (با کد کشور)
     * @param string $number شماره مقصد برای بررسی
     * @param string|null $apiKey API Key (اختیاری - در صورت عدم ارسال از config استفاده می‌شود)
     * @return array
     * @throws WhatsAppException
     */
    public function checkNumber(string $sender, string $number, ?string $apiKey = null): array
    {
        $apiKey = $apiKey ?? $this->apiKey;

        if (empty($apiKey)) {
            throw new WhatsAppException('API Key تنظیم نشده است. لطفاً WHATSAPP_API_KEY را در فایل .env تنظیم کنید یا به عنوان پارامتر ارسال کنید.');
        }

        $sender = $this->validatePhoneNumber($sender, 'شماره دستگاه');
        $number = $this->validatePhoneNumber($number, 'شماره مقصد');

        $data = [
            'api_key' => $apiKey,
            'sender' => $sender,
            'number' => $number,
        ];

        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/check-number', $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json() ?? $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            throw new WhatsAppException('خطا در بررسی شماره واتساپ: ' . $e->getMessage());
        }
    }

    /**
     * دریافت URL پایه
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * دریافت API Key
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}

