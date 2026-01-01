# پکیج BluPay (درگاه پرداخت سامان/SEP) برای Laravel

## درباره این پکیج

پکیج BluPay در ابتدا به‌عنوان یک ابزار داخلی برای استفاده شخصی در پروژه‌های واقعی توسعه داده شد.
سپس با هدف تسهیل فرآیند پیاده‌سازی و کاهش پیچیدگی فنی برای سایر تیم‌های توسعه و پذیرندگان،
به‌صورت یک پکیج مستقل و متن‌باز همراه با مستندات شفاف منتشر گردید.


این پکیج یک راهکار سبک و ساده برای اتصال به **درگاه پرداخت اینترنتی سامان (SEP)** است و رفتار **pg-neo** را نیز پوشش می‌دهد.

## قابلیت‌ها

- دریافت توکن (JSON POST)
- هدایت کاربر به درگاه (POST فرم یا لینک GET)
- تایید تراکنش (Verify)
- برگشت تراکنش (Reverse)
- پشتیبانی از pg-neo: خواندن آدرس درگاه از هدر `X-IPG-Url` در صورت وجود

## پیش‌نیازها

- PHP >= 8.1
- Laravel 10 / 11 / 12
- guzzlehttp/guzzle ^7

## نصب

```bash
composer require lidlike/blupay
php artisan vendor:publish --tag=blupay-config
```

## تنظیمات

در `.env` قرار بده:

```env
BLUPAY_TERMINAL_ID=2015
BLUPAY_TIMEOUT=30
```

(اختیاری) اگر خواستی URLها را override کنی:

```env
BLUPAY_TOKEN_URL=https://sep.shaparak.ir/onlinepg/onlinepg
BLUPAY_PAY_URL=https://sep.shaparak.ir/OnlinePG/OnlinePG
BLUPAY_SEND_URL=https://sep.shaparak.ir/OnlinePG/SendToken
BLUPAY_VERIFY_URL=https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/VerifyTransaction
BLUPAY_REVERSE_URL=https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/ReverseTransaction
```

## نحوه استفاده

### ۱) دریافت توکن

```php
use BluPay;

$tokenResp = BluPay::driver()->requestToken(
    amount: 12000,
    resNum: 'ORDER-123',
    redirectUrl: route('pay.callback'),
    cellNumber: '09120000000'
);

if (!$tokenResp->success) {
    return response()->json([
        'errorCode' => $tokenResp->errorCode,
        'errorDesc' => $tokenResp->errorDesc,
    ], 422);
}

$token = $tokenResp->token;
```

### ۲) هدایت کاربر به درگاه

#### روش A: لینک GET (SendToken)

```php
return redirect()->away(
    BluPay::driver()->redirectUrl($token)
);
```

#### روش B: فرم POST (OnlinePG)

اگر می‌خواهی با فرم POST کاربر را هدایت کنی، می‌توانی از endpoint مربوط به `OnlinePG` استفاده کنی.

### ۳) Callback و Verify

```php
use BluPay;
use Illuminate\Http\Request;

Route::post('/pay/callback', function (Request $request) {
    $refNum = $request->input('RefNum');

    $verify = BluPay::driver()->verify($refNum);

    if ($verify->success !== true) {
        return response()->json([
            'ok' => false,
            'resultCode' => $verify->resultCode,
            'resultDescription' => $verify->resultDescription,
        ], 400);
    }

    // نکته مهم: حتما مبلغ Verify را با مبلغ مورد انتظار خودت تطبیق بده
    return response()->json([
        'ok' => true,
        'transactionDetail' => $verify->transactionDetail,
    ]);
})->name('pay.callback');
```

### ۴) Reverse

```php
$reverse = BluPay::driver()->reverse($refNum);

if (!$reverse->success) {
    // مدیریت خطا
}
```

## پشتیبانی از pg-neo (مهم)

در حالت pg-neo ممکن است بعد از دریافت توکن، آدرس درگاه در هدر پاسخ برگردد:

- Header: `X-IPG-Url`
- Example: `https://neo-pg.sep.ir/transaction/init`

این پکیج مقدار آن را در دسترس می‌گذارد:

```php
$tokenResp->ipgUrlFromHeader;
```

## لایسنس

MIT


## سلب مسئولیت

این پکیج یک پیاده‌سازی مستقل و متن‌باز بر اساس مستندات فنی منتشرشده
درگاه پرداخت سامان (SEP) است و محصول رسمی شرکت پرداخت الکترونیک سامان
محسوب نمی‌شود.