# WhatsApp Module for Laravel

یک ماژول کامپوزر برای لاراول که امکان ارسال پیام واتساپ را فراهم می‌کند.

## نصب

```bash
composer require vendor/whatsapp-module
```

## پیکربندی

### 1. انتشار فایل تنظیمات

```bash
php artisan vendor:publish --tag=whatsapp-config
```

### 2. تنظیم متغیرهای محیطی

فایل `.env` را باز کرده و مقادیر زیر را اضافه کنید:

```env
WHATSAPP_BASE_URL=https://wa.ezlearn.store
WHATSAPP_API_KEY=your_api_key_here
WHATSAPP_DEFAULT_SENDER=your_sender_number
WHATSAPP_TIMEOUT=30
```

## استفاده

### استفاده از Facade

```php
use Vendor\WhatsAppModule\Facades\WhatsApp;

// ارسال پیام متنی
// توجه: شماره‌ها باید با کد کشور باشند (مثال: 989123456789 برای ایران)
$result = WhatsApp::sendMessage(
    '989123456789',        // شماره گیرنده (با کد کشور)
    'سلام، این یک پیام تست است',  // متن پیام
    '62888123456'          // شماره فرستنده (با کد کشور - اختیاری)
);

// ارسال رسانه (عکس، ویدیو، صدا، سند)
$result = WhatsApp::sendMedia(
    '989123456789',        // شماره گیرنده (با کد کشور)
    'image',               // نوع رسانه: image, video, audio, document
    'https://example.com/image.jpg',  // URL مستقیم رسانه
    'این یک تصویر تست است',  // توضیحات (اختیاری)
    '62888123456'          // شماره فرستنده (با کد کشور - اختیاری)
);

// ارسال نظرسنجی (Poll)
$result = WhatsApp::sendPoll(
    '989123456789',        // شماره گیرنده (با کد کشور)
    'کدام رنگ را ترجیح می‌دهید؟',  // سوال نظرسنجی
    ['قرمز', 'آبی', 'زرد'],  // گزینه‌های نظرسنجی (حداقل 2 گزینه)
    false,                 // countable: false = چند گزینه، true = فقط یک گزینه
    '62888123456'          // شماره فرستنده (با کد کشور - اختیاری)
);

// ارسال استیکر
$result = WhatsApp::sendSticker(
    '989123456789',        // شماره گیرنده (با کد کشور)
    'https://example.com/sticker.webp',  // URL مستقیم استیکر
    '62888123456'          // شماره فرستنده (با کد کشور - اختیاری)
);

// ارسال پیام با دکمه‌های تعاملی
$result = WhatsApp::sendButton(
    '989123456789',        // شماره گیرنده (با کد کشور)
    'سلام، این یک پیام با دکمه است',  // متن پیام
    [                      // آرایه دکمه‌ها (حداکثر 5 دکمه)
        [
            'type' => 'reply',
            'displayText' => 'پاسخ'
        ],
        [
            'type' => 'call',
            'displayText' => 'تماس',
            'phoneNumber' => '989123456789'
        ],
        [
            'type' => 'url',
            'displayText' => 'بازدید',
            'url' => 'https://example.com'
        ],
        [
            'type' => 'copy',
            'displayText' => 'کپی',
            'copyCode' => 'CODE123'
        ]
    ],
    'https://example.com/image.jpg',  // URL تصویر یا ویدیو (الزامی)
    'متن footer اختیاری',  // footer (اختیاری)
    '62888123456'          // شماره فرستنده (با کد کشور - اختیاری)
);

// تولید QR Code برای اتصال دستگاه
$result = WhatsApp::generateQR(
    '989123456789',        // شماره دستگاه (با کد کشور)
    false                  // force: اگر true باشد، در صورت عدم وجود دستگاه، ایجاد می‌شود
);

// بررسی وضعیت پاسخ
if ($result['success']) {
    if (isset($result['processing'])) {
        // در حال پردازش - باید دوباره درخواست بزنید
        echo "در حال پردازش...";
    } elseif (isset($result['connected'])) {
        // دستگاه قبلاً متصل است
        echo "دستگاه قبلاً متصل است!";
    } elseif (isset($result['qrcode'])) {
        // QR Code آماده است
        echo "QR Code: " . $result['qrcode'];
        // می‌توانید QR Code را نمایش دهید
    }
    } else {
        echo "خطا: " . $result['message'];
    }
}

// قطع اتصال دستگاه از واتساپ
$result = WhatsApp::disconnectDevice(
    '989123456789'  // شماره دستگاه (با کد کشور)
);

if ($result['success']) {
    echo "اتصال دستگاه با موفقیت قطع شد!";
} else {
    echo "خطا: " . $result['error'];
}

// دریافت اطلاعات دستگاه
$deviceInfo = WhatsApp::getDeviceInfo('989123456789');

// ایجاد دستگاه جدید
$createdDevice = WhatsApp::createDevice(
    '989123456789',
    'https://example.com/webhook'
);

// بررسی فعال بودن شماره قبل از ارسال
$checkNumber = WhatsApp::checkNumber(
    '989123456789',  // شماره دستگاه شما
    '989123456780'   // شماره مقصد
);

if (($checkNumber['data']['msg']['exists'] ?? false) === true) {
    // شماره واتساپ فعال است و می‌توان پیام ارسال کرد
}

### استفاده از Service Container

```php
use Vendor\WhatsAppModule\Services\WhatsAppService;

$whatsapp = app('whatsapp');

$result = $whatsapp->sendMessage(
    '989123456789',        // شماره گیرنده (با کد کشور)
    'سلام، این یک پیام تست است'
);

// ارسال رسانه
$result = $whatsapp->sendMedia(
    '989123456789',
    'image',
    'https://example.com/image.jpg',
    'توضیحات تصویر'
);

// ارسال نظرسنجی
$result = $whatsapp->sendPoll(
    '989123456789',
    'سوال نظرسنجی',
    ['گزینه 1', 'گزینه 2', 'گزینه 3'],
    false
);

// ارسال استیکر
$result = $whatsapp->sendSticker(
    '989123456789',
    'https://example.com/sticker.webp'
);

// ارسال پیام با دکمه
$result = $whatsapp->sendButton(
    '989123456789',
    'متن پیام',
    [
        ['type' => 'reply', 'displayText' => 'پاسخ'],
        ['type' => 'url', 'displayText' => 'بازدید', 'url' => 'https://example.com']
    ],
    'https://example.com/image.jpg'
);
```

### استفاده از Dependency Injection

```php
use Vendor\WhatsAppModule\Services\WhatsAppService;

class YourController
{
    public function __construct(
        protected WhatsAppService $whatsapp
    ) {}

    public function sendMessage()
    {
        $result = $this->whatsapp->sendMessage(
            '989123456789',        // شماره گیرنده (با کد کشور)
            'سلام، این یک پیام تست است'
        );
    }

    public function sendMedia()
    {
        $result = $this->whatsapp->sendMedia(
            '989123456789',
            'image',
            'https://example.com/image.jpg',
            'توضیحات تصویر'
        );
    }

    public function sendPoll()
    {
        $result = $this->whatsapp->sendPoll(
            '989123456789',
            'سوال نظرسنجی',
            ['گزینه 1', 'گزینه 2'],
            false
        );
    }

    public function sendSticker()
    {
        $result = $this->whatsapp->sendSticker(
            '989123456789',
            'https://example.com/sticker.webp'
        );
    }

    public function sendButton()
    {
        $result = $this->whatsapp->sendButton(
            '989123456789',
            'متن پیام',
            [
                ['type' => 'reply', 'displayText' => 'پاسخ'],
                ['type' => 'url', 'displayText' => 'بازدید', 'url' => 'https://example.com']
            ],
            'https://example.com/image.jpg',
            'متن footer'
        );
    }
}
```

## API Endpoints

پس از نصب ماژول، می‌توانید از API endpoints زیر استفاده کنید:

### ارسال پیام متنی

**POST** `/api/whatsapp/send-message`

**Request Body:**
```json
{
    "number": "989123456789",
    "message": "سلام، این یک پیام تست است",
    "sender": "62888123456"  // اختیاری - باید با کد کشور باشد
}
```

**Response:**
```json
{
    "success": true,
    "message": "پیام با موفقیت ارسال شد",
    "data": {
        // پاسخ از API واتساپ
    }
}
```

### ارسال رسانه (عکس، ویدیو، صدا، سند)

**POST** `/api/whatsapp/send-media`

**Request Body:**
```json
{
    "number": "989123456789",
    "media_type": "image",
    "url": "https://example.com/image.jpg",
    "caption": "این یک تصویر تست است",
    "sender": "62888123456"  // اختیاری - باید با کد کشور باشد
}
```

**نکته مهم:** 
- `media_type` باید یکی از این مقادیر باشد: `image`, `video`, `audio`, `document`
- `url` باید یک لینک مستقیم باشد (نه از Google Drive یا cloud storage)
- `caption` اختیاری است

**Response:**
```json
{
    "success": true,
    "message": "رسانه با موفقیت ارسال شد",
    "data": {
        // پاسخ از API واتساپ
    }
}
```

### ارسال نظرسنجی (Poll)

**POST** `/api/whatsapp/send-poll`

**Request Body:**
```json
{
    "number": "989123456789",
    "name": "کدام رنگ را ترجیح می‌دهید؟",
    "option": ["قرمز", "آبی", "زرد"],
    "countable": false,
    "sender": "62888123456"  // اختیاری - باید با کد کشور باشد
}
```

**نکته مهم:** 
- `name`: نام یا سوال نظرسنجی
- `option`: آرایه گزینه‌ها (حداقل 2 گزینه الزامی است)
- `countable`: `true` = فقط یک گزینه قابل انتخاب، `false` = چند گزینه قابل انتخاب
- `sender`: اختیاری - در صورت عدم ارسال از پیش‌فرض استفاده می‌شود

**Response:**
```json
{
    "success": true,
    "message": "نظرسنجی با موفقیت ارسال شد",
    "data": {
        // پاسخ از API واتساپ
    }
}
```

### ارسال استیکر

**POST** `/api/whatsapp/send-sticker`

**Request Body:**
```json
{
    "number": "989123456789",
    "url": "https://example.com/sticker.webp",
    "sender": "62888123456"  // اختیاری - باید با کد کشور باشد
}
```

**نکته مهم:** 
- `url` باید یک لینک مستقیم باشد (نه از Google Drive یا cloud storage)
- فرمت استیکر معمولاً `.webp` است

**Response:**
```json
{
    "success": true,
    "message": "استیکر با موفقیت ارسال شد",
    "data": {
        // پاسخ از API واتساپ
    }
}
```

### ارسال پیام با دکمه‌های تعاملی

**POST** `/api/whatsapp/send-button`

**Request Body:**
```json
{
    "number": "989123456789",
    "message": "سلام، این یک پیام با دکمه است",
    "button": [
        {
            "type": "reply",
            "displayText": "پاسخ"
        },
        {
            "type": "call",
            "displayText": "تماس",
            "phoneNumber": "989123456789"
        },
        {
            "type": "url",
            "displayText": "بازدید",
            "url": "https://example.com"
        },
        {
            "type": "copy",
            "displayText": "کپی",
            "copyCode": "CODE123"
        }
    ],
    "url": "https://example.com/image.jpg",
    "footer": "متن footer اختیاری",
    "sender": "62888123456"  // اختیاری - باید با کد کشور باشد
}
```

**نکته مهم:** 
- `message`: متن پیام (الزامی)
- `button`: آرایه دکمه‌ها (حداکثر 5 دکمه - الزامی)
- `url`: URL تصویر یا ویدیو (الزامی - برای اطمینان از ارسال)
- `footer`: متن footer (اختیاری)
- انواع دکمه‌ها:
  - `reply`: فقط `type` و `displayText` نیاز دارد
  - `call`: نیاز به `phoneNumber` دارد
  - `url`: نیاز به `url` دارد
  - `copy`: نیاز به `copyCode` دارد

**Response:**
```json
{
    "success": true,
    "message": "پیام با دکمه‌ها با موفقیت ارسال شد",
    "data": {
        // پاسخ از API واتساپ
    }
}
```

### تولید QR Code برای اتصال دستگاه

**POST** `/api/whatsapp/generate-qr`

**Request Body:**
```json
{
    "device": "989123456789",
    "force": false  // اختیاری - اگر true باشد، در صورت عدم وجود دستگاه، ایجاد می‌شود
}
```

**نکته مهم:** 
- `device`: شماره دستگاه **با کد کشور** (الزامی)
- `force`: اگر `true` باشد، در صورت عدم وجود دستگاه، ایجاد می‌شود (اختیاری)

**Response ها:**

1. **در حال پردازش** (نیاز به درخواست مجدد):
```json
{
    "success": true,
    "processing": true,
    "message": "Processing",
    "data": {
        "status": "processing",
        "message": "Processing"
    }
}
```

2. **QR Code آماده است**:
```json
{
    "success": true,
    "qrcode": "data:image/png;base64,iVBORw0KGgo...",
    "message": "Please scann qrcode",
    "data": {
        "status": false,
        "qrcode": "data:image/png;base64,...",
        "message": "Please scann qrcode"
    }
}
```

3. **دستگاه قبلاً متصل است**:
```json
{
    "success": true,
    "connected": true,
    "message": "Device already connected!",
    "data": {
        "status": false,
        "msg": "Device already connected!"
    }
}
```

4. **خطا**:
```json
{
    "success": false,
    "message": "Invalid data!",
    "errors": {},
    "data": {
        "status": false,
        "msg": "Invalid data!",
        "errors": {}
    }
}
```

**نکته:** 
- اگر پاسخ `processing` باشد، باید دوباره endpoint را فراخوانی کنید تا نتیجه نهایی را دریافت کنید
- بعد از اسکن QR Code، دوباره endpoint را فراخوانی کنید تا وضعیت نهایی را بررسی کنید
- QR Code به صورت base64 encoded است و می‌توانید مستقیماً در تگ `<img>` استفاده کنید

### قطع اتصال دستگاه از واتساپ

**POST** `/api/whatsapp/logout-device`

**Request Body:**
```json
{
    "sender": "989123456789"
}
```

**نکته مهم:** 
- `sender`: شماره دستگاه **با کد کشور** (الزامی)

**Response:**
```json
{
    "success": true,
    "message": "اتصال دستگاه با موفقیت قطع شد",
    "data": {
        // پاسخ از API واتساپ
    }
}
```

### دریافت اطلاعات دستگاه

**POST** `/api/whatsapp/info-device`

**Request Body:**
```json
{
    "number": "989123456789",
    "api_key": "اختیاری - در صورت عدم ارسال از config استفاده می‌شود"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "status": true,
        "info": [
            {
                "id": 1,
                "user_id": 1,
                "body": "628122xxxxxx",
                "status": "Disconnect",
                "created_at": "2024-08-16T11:07:27.000000Z"
            }
        ]
    }
}
```

### ایجاد دستگاه جدید

**POST** `/api/whatsapp/create-device`

**Request Body:**
```json
{
    "sender": "6282298859671",
    "urlwebhook": "https://yourdomain.com/webhook",
    "api_key": "اختیاری"
}
```

**نکته مهم:**
- `sender` باید یکتا باشد و حداقل 8 رقم/کاراکتر داشته باشد (پیشنهاد: شماره با کد کشور).
- `urlwebhook` اختیاری است ولی باید URL معتبر باشد.

### بررسی فعال بودن شماره (Check Number)

**POST** `/api/whatsapp/check-number`

**Request Body:**
```json
{
    "sender": "989123456789",   // شماره دستگاه شما
    "number": "989123456780",   // شماره مقصد
    "api_key": "اختیاری"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "status": true,
        "msg": {
            "exists": true,
            "jid": "201111347197@s.whatsapp.net"
        }
    }
}
```

> توصیه می‌شود قبل از هر ارسال پیام، از این API برای اطمینان از فعال بودن شماره مقصد استفاده کنید.

### وبهوک (دریافت پیام‌های ورودی)

اگر هنگام ایجاد دستگاه مقدار `urlwebhook` را تنظیم کنید، هر پیام ورودی به دستگاه شما به همان URL ارسال می‌شود. بدنه درخواست POST به شکل زیر است:

```json
{
    "device": "your sender/device",
    "message": "message text",
    "from": "the number of the whatsapp sender",
    "name": "the name of the sender",
    "participant": "sender number if group",
    "ppUrl": "url profile picture sender",
    "media": [
        {
            "caption": "caption, equal to message",
            "fileName": "xxxx.xx",
            "stream": [
                {
                    "type": "Buffer",
                    "data": "xxxx"
                }
            ],
            "mimetype": "image/jpeg"
        }
    ]
}
```

- اگر پیام متنی باشد، فیلد `media` مقدار `null` خواهد داشت.
- برای رسانه‌ها، `stream` شامل داده‌های Base64/Buffer و `mimetype` نوع فایل (image/document/audio و ...) است.
- از این اطلاعات می‌توانید برای ساخت چت‌بات، پاسخگویی خودکار یا ذخیره پیام‌های دریافتی استفاده کنید.

## متدهای موجود

### `sendMessage(string $number, string $message, ?string $sender = null)`

ارسال پیام متنی به شماره مشخص شده.

**پارامترها:**
- `$number`: شماره گیرنده **با کد کشور** (مثال: `989123456789` برای ایران یا `62888123456`)
- `$message`: متن پیام
- `$sender`: شماره فرستنده **با کد کشور** (اختیاری - در صورت عدم ارسال از پیش‌فرض استفاده می‌شود)

**نکته:** شماره‌ها باید همیشه با کد کشور باشند. ماژول به صورت خودکار اعتبارسنجی می‌کند.

### `sendMedia(string $number, string $mediaType, string $url, ?string $caption = null, ?string $sender = null)`

ارسال رسانه (عکس، ویدیو، صدا، سند) به شماره مشخص شده.

**پارامترها:**
- `$number`: شماره گیرنده **با کد کشور** (مثال: `989123456789`)
- `$mediaType`: نوع رسانه - باید یکی از این مقادیر باشد: `image`, `video`, `audio`, `document`
- `$url`: آدرس مستقیم رسانه (باید لینک مستقیم باشد، نه از Google Drive یا cloud storage)
- `$caption`: متن توضیحات (اختیاری)
- `$sender`: شماره فرستنده **با کد کشور** (اختیاری - در صورت عدم ارسال از پیش‌فرض استفاده می‌شود)

**مثال:**
```php
// ارسال عکس
WhatsApp::sendMedia('989123456789', 'image', 'https://example.com/image.jpg', 'توضیحات');

// ارسال ویدیو
WhatsApp::sendMedia('989123456789', 'video', 'https://example.com/video.mp4');

// ارسال صدا
WhatsApp::sendMedia('989123456789', 'audio', 'https://example.com/audio.mp3');

// ارسال سند
WhatsApp::sendMedia('989123456789', 'document', 'https://example.com/file.pdf', 'فایل PDF');
```

**برمی‌گرداند:**
```php
[
    'success' => true/false,
    'data' => [...],  // در صورت موفقیت
    'error' => [...], // در صورت خطا
    'status' => 200   // کد وضعیت HTTP
]
```

### `sendPoll(string $number, string $name, array $options, bool $countable = false, ?string $sender = null)`

ارسال نظرسنجی به شماره مشخص شده.

**پارامترها:**
- `$number`: شماره گیرنده **با کد کشور** (مثال: `989123456789`)
- `$name`: نام یا سوال نظرسنجی
- `$options`: آرایه گزینه‌های نظرسنجی (حداقل 2 گزینه الزامی است)
- `$countable`: آیا فقط یک گزینه قابل انتخاب است؟ (`true` = فقط یک گزینه، `false` = چند گزینه)
- `$sender`: شماره فرستنده **با کد کشور** (اختیاری - در صورت عدم ارسال از پیش‌فرض استفاده می‌شود)

**مثال:**
```php
// نظرسنجی با چند گزینه قابل انتخاب
WhatsApp::sendPoll(
    '989123456789',
    'کدام رنگ را ترجیح می‌دهید؟',
    ['قرمز', 'آبی', 'زرد', 'سبز'],
    false  // چند گزینه قابل انتخاب
);

// نظرسنجی با فقط یک گزینه قابل انتخاب
WhatsApp::sendPoll(
    '989123456789',
    'آیا از محصول راضی هستید؟',
    ['بله', 'خیر'],
    true  // فقط یک گزینه قابل انتخاب
);
```

**برمی‌گرداند:**
```php
[
    'success' => true/false,
    'data' => [...],  // در صورت موفقیت
    'error' => [...], // در صورت خطا
    'status' => 200   // کد وضعیت HTTP
]
```

### `sendSticker(string $number, string $url, ?string $sender = null)`

ارسال استیکر به شماره مشخص شده.

**پارامترها:**
- `$number`: شماره گیرنده **با کد کشور** (مثال: `989123456789`)
- `$url`: آدرس مستقیم استیکر (باید لینک مستقیم باشد، نه از Google Drive یا cloud storage)
- `$sender`: شماره فرستنده **با کد کشور** (اختیاری - در صورت عدم ارسال از پیش‌فرض استفاده می‌شود)

**مثال:**
```php
WhatsApp::sendSticker(
    '989123456789',
    'https://example.com/sticker.webp'
);
```

**برمی‌گرداند:**
```php
[
    'success' => true/false,
    'data' => [...],  // در صورت موفقیت
    'error' => [...], // در صورت خطا
    'status' => 200   // کد وضعیت HTTP
]
```

### `sendButton(string $number, string $message, array $buttons, string $imageUrl, ?string $footer = null, ?string $sender = null)`

ارسال پیام با دکمه‌های تعاملی به شماره مشخص شده.

**پارامترها:**
- `$number`: شماره گیرنده **با کد کشور** (مثال: `989123456789`)
- `$message`: متن پیام
- `$buttons`: آرایه دکمه‌ها (حداکثر 5 دکمه)
- `$imageUrl`: آدرس تصویر یا ویدیو (الزامی - برای اطمینان از ارسال)
- `$footer`: متن footer (اختیاری)
- `$sender`: شماره فرستنده **با کد کشور** (اختیاری - در صورت عدم ارسال از پیش‌فرض استفاده می‌شود)

**ساختار دکمه‌ها:**
```php
[
    // دکمه Reply
    [
        'type' => 'reply',
        'displayText' => 'متن دکمه'
    ],
    
    // دکمه Call
    [
        'type' => 'call',
        'displayText' => 'تماس',
        'phoneNumber' => '989123456789'  // با کد کشور
    ],
    
    // دکمه URL
    [
        'type' => 'url',
        'displayText' => 'بازدید',
        'url' => 'https://example.com'
    ],
    
    // دکمه Copy
    [
        'type' => 'copy',
        'displayText' => 'کپی',
        'copyCode' => 'CODE123'
    ]
]
```

**مثال:**
```php
WhatsApp::sendButton(
    '989123456789',
    'سلام، این یک پیام با دکمه است',
    [
        [
            'type' => 'reply',
            'displayText' => 'پاسخ'
        ],
        [
            'type' => 'call',
            'displayText' => 'تماس',
            'phoneNumber' => '989123456789'
        ],
        [
            'type' => 'url',
            'displayText' => 'بازدید سایت',
            'url' => 'https://example.com'
        ]
    ],
    'https://example.com/image.jpg',  // تصویر الزامی است
    'متن footer اختیاری'
);
```

**برمی‌گرداند:**
```php
[
    'success' => true/false,
    'data' => [...],  // در صورت موفقیت
    'error' => [...], // در صورت خطا
    'status' => 200   // کد وضعیت HTTP
]
```

### `generateQR(string $device, bool $force = false)`

تولید QR Code برای اتصال دستگاه به واتساپ.

**پارامترها:**
- `$device`: شماره دستگاه **با کد کشور** (مثال: `989123456789`)
- `$force`: اگر `true` باشد، در صورت عدم وجود دستگاه، ایجاد می‌شود (پیش‌فرض: `false`)

**مثال:**
```php
// تولید QR Code
$result = WhatsApp::generateQR('989123456789', false);

// بررسی وضعیت
if ($result['success']) {
    if (isset($result['processing'])) {
        // در حال پردازش - باید دوباره درخواست بزنید
        // می‌توانید با sleep و retry این کار را انجام دهید
        sleep(2);
        $result = WhatsApp::generateQR('989123456789', false);
    }
    
    if (isset($result['qrcode'])) {
        // نمایش QR Code
        echo '<img src="' . $result['qrcode'] . '" alt="QR Code">';
        
        // بعد از اسکن، دوباره بررسی کنید
        sleep(5);
        $result = WhatsApp::generateQR('989123456789', false);
        
        if (isset($result['connected'])) {
            echo "دستگاه با موفقیت متصل شد!";
        }
    }
    
    if (isset($result['connected'])) {
        echo "دستگاه قبلاً متصل است!";
    }
}
```

**برمی‌گرداند:**

در حال پردازش:
```php
[
    'success' => true,
    'processing' => true,
    'message' => 'Processing',
    'data' => [...],
    'status' => 200
]
```

QR Code آماده:
```php
[
    'success' => true,
    'qrcode' => 'data:image/png;base64,...',
    'message' => 'Please scann qrcode',
    'data' => [...],
    'status' => 200
]
```

دستگاه متصل:
```php
[
    'success' => true,
    'connected' => true,
    'message' => 'Device already connected!',
    'data' => [...],
    'status' => 200
]
```

خطا:
```php
[
    'success' => false,
    'message' => 'Invalid data!',
    'errors' => [...],
    'data' => [...],
    'status' => 200
]
```

### `disconnectDevice(string $sender)`

قطع اتصال دستگاه از واتساپ.

**پارامترها:**
- `$sender`: شماره دستگاه برای قطع اتصال **با کد کشور** (مثال: `989123456789`)

**مثال:**
```php
$result = WhatsApp::disconnectDevice('989123456789');

if ($result['success']) {
    echo "اتصال دستگاه با موفقیت قطع شد!";
} else {
    echo "خطا: " . $result['error'];
}
```

**برمی‌گرداند:**
```php
[
    'success' => true/false,
    'data' => [...],  // در صورت موفقیت
    'error' => [...], // در صورت خطا
    'status' => 200   // کد وضعیت HTTP
]
```

### `getDeviceInfo(string $number, ?string $apiKey = null)`

دریافت اطلاعات دستگاه متصل.

**پارامترها:**
- `$number`: شماره دستگاه **با کد کشور**
- `$apiKey`: در صورت نیاز می‌توان API Key دیگری را ارسال کرد (اختیاری)

### `createDevice(string $sender, ?string $urlWebhook = null, ?string $apiKey = null)`

ایجاد دستگاه جدید برای ارسال پیام‌ها.

**پارامترها:**
- `$sender`: شماره دستگاه **با کد کشور** (حداقل 8 رقم)
- `$urlWebhook`: آدرس وبهوک (اختیاری)
- `$apiKey`: API Key سفارشی (اختیاری)

### `checkNumber(string $sender, string $number, ?string $apiKey = null)`

بررسی می‌کند که آیا شماره مقصد در واتساپ فعال است یا خیر.

**پارامترها:**
- `$sender`: شماره دستگاه شما **با کد کشور**
- `$number`: شماره مقصد برای بررسی
- `$apiKey`: API Key (اختیاری)

### `setBaseUrl(string $url)`

تنظیم URL پایه API (برای استفاده موقت).

### `setApiKey(string $apiKey)`

تنظیم API Key (برای استفاده موقت).

### `setDefaultSender(string $sender)`

تنظیم شماره فرستنده پیش‌فرض (برای استفاده موقت).

## خطاها

ماژول از استثنای اختصاصی `WhatsAppException` استفاده می‌کند:

```php
use Vendor\WhatsAppModule\Exceptions\WhatsAppException;

try {
    WhatsApp::sendMessage('989123456789', 'پیام تست');
} catch (WhatsAppException $e) {
    // مدیریت خطا
    echo $e->getMessage();
}

// خطاهای رایج:
// - "شماره گیرنده باید شامل کد کشور باشد..." (اگر شماره بدون کد کشور باشد)
// - "نوع رسانه نامعتبر است..." (اگر media_type اشتباه باشد)
// - "URL رسانه نامعتبر است..." (اگر URL معتبر نباشد)
// - "نظرسنجی باید حداقل 2 گزینه داشته باشد..." (اگر گزینه‌ها کمتر از 2 باشد)
// - "نام یا سوال نظرسنجی نمی‌تواند خالی باشد..." (اگر name خالی باشد)
// - "URL تصویر یا ویدیو الزامی است..." (اگر imageUrl ارسال نشود)
// - "حداکثر 5 دکمه مجاز است..." (اگر بیشتر از 5 دکمه ارسال شود)
// - "نوع دکمه نامعتبر است..." (اگر type دکمه اشتباه باشد)
// - "شماره دستگاه باید شامل کد کشور باشد..." (اگر device بدون کد کشور باشد)
```

## نیازمندی‌ها

- PHP >= 8.1
- Laravel >= 10.0

## مجوز

MIT

