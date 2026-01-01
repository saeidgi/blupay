<?php

return [
    'terminal_id' => env('BLUPAY_TERMINAL_ID'),
    'timeout' => (int) env('BLUPAY_TIMEOUT', 30),

    // Default endpoints (may be overridden by pg-neo X-IPG-Url header after token request)
    'endpoints' => [
        // Token request endpoint
        'token'   => env('BLUPAY_TOKEN_URL', 'https://sep.shaparak.ir/onlinepg/onlinepg'),

        // Payment page endpoints (merchant-side redirect)
        'pay'     => env('BLUPAY_PAY_URL',   'https://sep.shaparak.ir/OnlinePG/OnlinePG'),
        'send'    => env('BLUPAY_SEND_URL',  'https://sep.shaparak.ir/OnlinePG/SendToken'),

        // Server-to-server endpoints
        'verify'  => env('BLUPAY_VERIFY_URL','https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/VerifyTransaction'),
        'reverse' => env('BLUPAY_REVERSE_URL','https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/ReverseTransaction'),
    ],
];
