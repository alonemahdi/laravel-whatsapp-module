# Project Brief: WhatsApp Module for Laravel

## هدف پروژه
ایجاد یک ماژول کامپوزر برای لاراول که امکان ارسال پیام واتساپ را فراهم می‌کند.

## محدوده پروژه
- ماژول کامپوزر قابل نصب در پروژه‌های لاراول
- API endpoints برای ارسال پیام واتساپ
- مدیریت اتصال به سرویس واتساپ با URL و API Key سراسری
- ارسال پیام متنی
- ارسال رسانه (عکس، ویدیو، صدا، سند)
- اعتبارسنجی شماره‌ها با کد کشور (برای sender و receiver)

## الزامات اولیه
- سازگار با Laravel 10.x و 11.x
- ساختار استاندارد Package Laravel
- API endpoints برای ارتباط با واتساپ
- مدیریت اتصال با استفاده از Config (URL و API Key سراسری)
- سرویس واتساپ: wa.ezlearn.store

## جزئیات API

### Send Message API
- **Endpoint**: `https://wa.ezlearn.store/send-message`
- **Method**: POST | GET
- **Parameters**:
  - `api_key` (string, required): API Key
  - `sender` (string, required): شماره دستگاه فرستنده (باید با کد کشور باشد)
  - `number` (string, required): شماره گیرنده (باید با کد کشور باشد، مثال: 989123456789|62888123456)
  - `message` (string, required): متن پیام

### Send Media API
- **Endpoint**: `https://wa.ezlearn.store/send-media`
- **Method**: POST | GET
- **Parameters**:
  - `api_key` (string, required): API Key
  - `sender` (string, required): شماره دستگاه فرستنده (باید با کد کشور باشد)
  - `number` (string, required): شماره گیرنده (باید با کد کشور باشد)
  - `media_type` (string, required): نوع رسانه (image, video, audio, document)
  - `url` (string, required): آدرس مستقیم رسانه (باید لینک مستقیم باشد)
  - `caption` (string, optional): توضیحات/پیام

## وضعیت فعلی
- ساختار اصلی ماژول پیاده‌سازی شده
- قابلیت ارسال پیام متنی اضافه شده
- آماده برای افزودن قابلیت‌های بیشتر

