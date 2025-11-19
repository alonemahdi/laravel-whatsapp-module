<?php

namespace Vendor\WhatsAppModule\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade برای دسترسی آسان به سرویس واتساپ
 *
 * @method static \Vendor\WhatsAppModule\Services\WhatsAppService setBaseUrl(string $url)
 * @method static \Vendor\WhatsAppModule\Services\WhatsAppService setApiKey(string $apiKey)
 * @method static \Vendor\WhatsAppModule\Services\WhatsAppService setDefaultSender(string $sender)
 * @method static array sendMessage(string $number, string $message, ?string $sender = null)
 * @method static array sendMedia(string $number, string $mediaType, string $url, ?string $caption = null, ?string $sender = null)
 * @method static array sendPoll(string $number, string $name, array $options, bool $countable = false, ?string $sender = null)
 * @method static array sendSticker(string $number, string $url, ?string $sender = null)
 * @method static array sendButton(string $number, string $message, array $buttons, string $imageUrl, ?string $footer = null, ?string $sender = null)
 * @method static array generateQR(string $device, bool $force = false)
 * @method static array disconnectDevice(string $sender)
 * @method static array getUserInfo(string $username, ?string $apiKey = null)
 * @method static string getBaseUrl()
 * @method static string getApiKey()
 */
class WhatsApp extends Facade
{
    /**
     * دریافت نام سرویس ثبت شده
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'whatsapp';
    }
}

