<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp API Configuration
    |--------------------------------------------------------------------------
    |
    | تنظیمات API واتساپ برای استفاده در ماژول
    |
    */

    // URL پایه سرویس واتساپ
    'base_url' => env('WHATSAPP_BASE_URL', 'https://wa.ezlearn.store'),

    // API Key برای احراز هویت
    'api_key' => env('WHATSAPP_API_KEY', ''),

    // شماره فرستنده پیش‌فرض (شماره دستگاه شما)
    'default_sender' => env('WHATSAPP_DEFAULT_SENDER', ''),

    // تنظیمات اضافی
    'timeout' => env('WHATSAPP_TIMEOUT', 30), // زمان انتظار برای درخواست‌ها (ثانیه)
];

