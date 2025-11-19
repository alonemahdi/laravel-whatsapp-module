<?php

use Illuminate\Support\Facades\Route;
use Vendor\WhatsAppModule\Http\Controllers\WhatsAppController;

/*
|--------------------------------------------------------------------------
| WhatsApp Module API Routes
|--------------------------------------------------------------------------
|
| مسیرهای API برای ماژول واتساپ
|
*/

Route::prefix('whatsapp')->group(function () {
    // ارسال پیام متنی
    Route::post('/send-message', [WhatsAppController::class, 'sendMessage']);
    
    // ارسال رسانه (عکس، ویدیو، صدا، سند)
    Route::post('/send-media', [WhatsAppController::class, 'sendMedia']);
    
    // ارسال نظرسنجی (Poll)
    Route::post('/send-poll', [WhatsAppController::class, 'sendPoll']);
    
    // ارسال استیکر
    Route::post('/send-sticker', [WhatsAppController::class, 'sendSticker']);
    
    // ارسال پیام با دکمه‌های تعاملی
    Route::post('/send-button', [WhatsAppController::class, 'sendButton']);
    
    // تولید QR Code برای اتصال دستگاه
    Route::post('/generate-qr', [WhatsAppController::class, 'generateQR']);
    
    // قطع اتصال دستگاه از واتساپ
    Route::post('/logout-device', [WhatsAppController::class, 'disconnectDevice']);
    
    // دریافت اطلاعات کاربر
    Route::post('/info-user', [WhatsAppController::class, 'getUserInfo']);
    Route::get('/info-user', [WhatsAppController::class, 'getUserInfo']);

    // دریافت اطلاعات دستگاه
    Route::post('/info-device', [WhatsAppController::class, 'getDeviceInfo']);
    Route::get('/info-device', [WhatsAppController::class, 'getDeviceInfo']);

    // ایجاد دستگاه جدید
    Route::post('/create-device', [WhatsAppController::class, 'createDevice']);
    Route::get('/create-device', [WhatsAppController::class, 'createDevice']);

    // بررسی فعال بودن شماره واتساپ
    Route::post('/check-number', [WhatsAppController::class, 'checkNumber']);
    Route::get('/check-number', [WhatsAppController::class, 'checkNumber']);
});

