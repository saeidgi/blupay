# LidLike BluPay (SEP / pg-neo) for Laravel

## About This Package

BluPay was initially developed as an internal utility for personal use in real-world projects.
To facilitate adoption and reduce integration effort for other development teams, it was later
refactored and published as an independent, open-source package with clear documentation.


A lightweight Laravel package for integrating **SEP (Saman Electronic Payment)** Internet Payment Gateway (IPG), including **pg-neo** behavior.

> 📘 **Persian Documentation:**  
> برای مطالعه راهنمای فارسی این پکیج، به فایل  
> [README.fa.md](README.fa.md)  
> مراجعه کنید.


## Features

- Request Token (JSON POST)
- Redirect customer to payment page (POST form or GET SendToken)
- Verify transaction
- Reverse transaction
- Supports pg-neo: reads payment URL from `X-IPG-Url` header when provided

## Requirements

- PHP >= 8.1
- Laravel 10 / 11 / 12
- guzzlehttp/guzzle ^7

## Installation

```bash
composer require saeidgi/blupay
php artisan vendor:publish --tag=blupay-config
```

## Configuration

Add to `.env`:

```env
BLUPAY_TERMINAL_ID=2015
BLUPAY_TIMEOUT=30
```

Optional endpoint overrides:

```env
BLUPAY_TOKEN_URL=https://sep.shaparak.ir/onlinepg/onlinepg
BLUPAY_PAY_URL=https://sep.shaparak.ir/OnlinePG/OnlinePG
BLUPAY_SEND_URL=https://sep.shaparak.ir/OnlinePG/SendToken
BLUPAY_VERIFY_URL=https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/VerifyTransaction
BLUPAY_REVERSE_URL=https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/ReverseTransaction
```

## Usage

### 1) Request Token

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

### 2) Redirect Customer to Payment Gateway

#### Option A: GET Redirect (SendToken)

```php
return redirect()->away(
    BluPay::driver()->redirectUrl($token)
);
```

#### Option B: POST Form Redirect (OnlinePG)

If you prefer POST redirect (HTML form), use the documented `OnlinePG` action endpoint.

### 3) Handle Callback + Verify

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

    // IMPORTANT: Compare verified amount with your expected amount before fulfilling the order.
    return response()->json([
        'ok' => true,
        'transactionDetail' => $verify->transactionDetail,
    ]);
})->name('pay.callback');
```

### 4) Reverse Transaction

```php
$reverse = BluPay::driver()->reverse($refNum);

if (!$reverse->success) {
    // handle failed reverse
}
```

## pg-neo Support (Important)

In pg-neo, after requesting token, the gateway base URL may be returned in response headers:

- Header: `X-IPG-Url`
- Example: `https://neo-pg.sep.ir/transaction/init`

This package exposes that header via:

```php
$tokenResp->ipgUrlFromHeader;
```

## License

MIT


## Disclaimer

This package is an independent open-source client implementation based on
publicly available technical documentation of the SEP payment gateway.
It is not an official product of Saman Electronic Payment and is provided
"as is" without any warranty.
