<?php

namespace Vendor\WhatsAppModule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Vendor\WhatsAppModule\Facades\WhatsApp;
use Vendor\WhatsAppModule\Exceptions\WhatsAppException;

/**
 * کنترلر برای API endpoints واتساپ
 */
class WhatsAppController
{
    /**
     * ارسال پیام متنی
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMessage(Request $request): JsonResponse
    {
        // اعتبارسنجی داده‌های ورودی
        $validated = $request->validate([
            'number' => 'required|string',
            'message' => 'required|string',
            'sender' => 'nullable|string',
        ]);

        try {
            $result = WhatsApp::sendMessage(
                $validated['number'],
                $validated['message'],
                $validated['sender'] ?? null
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'پیام با موفقیت ارسال شد',
                    'data' => $result['data'],
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'خطا در ارسال پیام',
                'error' => $result['error'],
            ], $result['status'] ?? 500);

        } catch (WhatsAppException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطای غیرمنتظره: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ارسال رسانه (عکس، ویدیو، صدا، سند)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendMedia(Request $request): JsonResponse
    {
        // اعتبارسنجی داده‌های ورودی
        $validated = $request->validate([
            'number' => 'required|string',
            'media_type' => 'required|string|in:image,video,audio,document',
            'url' => 'required|url',
            'caption' => 'nullable|string',
            'sender' => 'nullable|string',
        ]);

        try {
            $result = WhatsApp::sendMedia(
                $validated['number'],
                $validated['media_type'],
                $validated['url'],
                $validated['caption'] ?? null,
                $validated['sender'] ?? null
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'رسانه با موفقیت ارسال شد',
                    'data' => $result['data'],
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'خطا در ارسال رسانه',
                'error' => $result['error'],
            ], $result['status'] ?? 500);

        } catch (WhatsAppException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطای غیرمنتظره: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ارسال نظرسنجی (Poll)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendPoll(Request $request): JsonResponse
    {
        // اعتبارسنجی داده‌های ورودی
        $validated = $request->validate([
            'number' => 'required|string',
            'name' => 'required|string',
            'option' => 'required|array|min:2',
            'option.*' => 'required|string',
            'countable' => 'nullable|boolean',
            'sender' => 'nullable|string',
        ]);

        try {
            $result = WhatsApp::sendPoll(
                $validated['number'],
                $validated['name'],
                $validated['option'],
                $validated['countable'] ?? false,
                $validated['sender'] ?? null
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'نظرسنجی با موفقیت ارسال شد',
                    'data' => $result['data'],
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'خطا در ارسال نظرسنجی',
                'error' => $result['error'],
            ], $result['status'] ?? 500);

        } catch (WhatsAppException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطای غیرمنتظره: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ارسال استیکر
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendSticker(Request $request): JsonResponse
    {
        // اعتبارسنجی داده‌های ورودی
        $validated = $request->validate([
            'number' => 'required|string',
            'url' => 'required|url',
            'sender' => 'nullable|string',
        ]);

        try {
            $result = WhatsApp::sendSticker(
                $validated['number'],
                $validated['url'],
                $validated['sender'] ?? null
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'استیکر با موفقیت ارسال شد',
                    'data' => $result['data'],
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'خطا در ارسال استیکر',
                'error' => $result['error'],
            ], $result['status'] ?? 500);

        } catch (WhatsAppException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطای غیرمنتظره: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ارسال پیام با دکمه‌های تعاملی
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendButton(Request $request): JsonResponse
    {
        // اعتبارسنجی داده‌های ورودی
        $validated = $request->validate([
            'number' => 'required|string',
            'message' => 'required|string',
            'button' => 'required|array|max:5',
            'button.*.type' => 'required|string|in:reply,call,url,copy',
            'button.*.displayText' => 'required|string',
            'button.*.phoneNumber' => 'required_if:button.*.type,call|string',
            'button.*.url' => 'required_if:button.*.type,url|url',
            'button.*.copyCode' => 'required_if:button.*.type,copy|string',
            'url' => 'required|url',
            'footer' => 'nullable|string',
            'sender' => 'nullable|string',
        ]);

        try {
            $result = WhatsApp::sendButton(
                $validated['number'],
                $validated['message'],
                $validated['button'],
                $validated['url'],
                $validated['footer'] ?? null,
                $validated['sender'] ?? null
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'پیام با دکمه‌ها با موفقیت ارسال شد',
                    'data' => $result['data'],
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'خطا در ارسال دکمه',
                'error' => $result['error'],
            ], $result['status'] ?? 500);

        } catch (WhatsAppException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطای غیرمنتظره: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * تولید QR Code برای اتصال دستگاه
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateQR(Request $request): JsonResponse
    {
        // اعتبارسنجی داده‌های ورودی
        $validated = $request->validate([
            'device' => 'required|string',
            'force' => 'nullable|boolean',
        ]);

        try {
            $result = WhatsApp::generateQR(
                $validated['device'],
                $validated['force'] ?? false
            );

            // در حال پردازش
            if (isset($result['processing']) && $result['processing']) {
                return response()->json([
                    'success' => true,
                    'processing' => true,
                    'message' => $result['message'],
                    'data' => $result['data'],
                ], 200);
            }

            // دستگاه قبلاً متصل است
            if (isset($result['connected']) && $result['connected']) {
                return response()->json([
                    'success' => true,
                    'connected' => true,
                    'message' => $result['message'],
                    'data' => $result['data'],
                ], 200);
            }

            // QR Code آماده است
            if (isset($result['qrcode'])) {
                return response()->json([
                    'success' => true,
                    'qrcode' => $result['qrcode'],
                    'message' => $result['message'],
                    'data' => $result['data'],
                ], 200);
            }

            // خطا
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'خطا در تولید QR Code',
                    'errors' => $result['errors'] ?? [],
                    'error' => $result['error'] ?? null,
                ], $result['status'] ?? 500);
            }

            // پاسخ موفق
            return response()->json([
                'success' => true,
                'data' => $result['data'],
            ], 200);

        } catch (WhatsAppException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطای غیرمنتظره: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * قطع اتصال دستگاه از واتساپ
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function disconnectDevice(Request $request): JsonResponse
    {
        // اعتبارسنجی داده‌های ورودی
        $validated = $request->validate([
            'sender' => 'required|string',
        ]);

        try {
            $result = WhatsApp::disconnectDevice($validated['sender']);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'اتصال دستگاه با موفقیت قطع شد',
                    'data' => $result['data'],
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'خطا در قطع اتصال دستگاه',
                'error' => $result['error'],
            ], $result['status'] ?? 500);

        } catch (WhatsAppException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطای غیرمنتظره: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * دریافت اطلاعات کاربر
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserInfo(Request $request): JsonResponse
    {
        // اعتبارسنجی داده‌های ورودی
        $validated = $request->validate([
            'username' => 'required|string|regex:/^[a-zA-Z0-9_]+$/',
            'api_key' => 'nullable|string',
        ]);

        try {
            $result = WhatsApp::getUserInfo(
                $validated['username'],
                $validated['api_key'] ?? null
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'user' => $result['user'],
                    'data' => $result['data'],
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'خطا در دریافت اطلاعات کاربر',
                'error' => $result['error'] ?? null,
            ], $result['status'] ?? 500);

        } catch (WhatsAppException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطای غیرمنتظره: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * دریافت اطلاعات دستگاه متصل
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDeviceInfo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number' => 'required|string|regex:/^\d{8,15}$/',
            'api_key' => 'nullable|string',
        ]);

        try {
            $result = WhatsApp::getDeviceInfo(
                $validated['number'],
                $validated['api_key'] ?? null
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت اطلاعات دستگاه',
                'error' => $result['error'] ?? null,
            ], $result['status'] ?? 500);

        } catch (WhatsAppException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطای غیرمنتظره: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ایجاد دستگاه جدید
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createDevice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sender' => 'required|string|regex:/^\d{8,15}$/',
            'urlwebhook' => 'nullable|url',
            'api_key' => 'nullable|string',
        ]);

        try {
            $result = WhatsApp::createDevice(
                $validated['sender'],
                $validated['urlwebhook'] ?? null,
                $validated['api_key'] ?? null
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'دستگاه جدید با موفقیت ایجاد شد',
                    'data' => $result['data'],
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد دستگاه جدید',
                'error' => $result['error'] ?? null,
            ], $result['status'] ?? 500);

        } catch (WhatsAppException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطای غیرمنتظره: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * بررسی فعال بودن شماره در واتساپ
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkNumber(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sender' => 'required|string|regex:/^\d{8,15}$/',
            'number' => 'required|string|regex:/^\d{8,15}$/',
            'api_key' => 'nullable|string',
        ]);

        try {
            $result = WhatsApp::checkNumber(
                $validated['sender'],
                $validated['number'],
                $validated['api_key'] ?? null
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'خطا در بررسی شماره واتساپ',
                'error' => $result['error'] ?? null,
            ], $result['status'] ?? 500);

        } catch (WhatsAppException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطای غیرمنتظره: ' . $e->getMessage(),
            ], 500);
        }
    }
}

