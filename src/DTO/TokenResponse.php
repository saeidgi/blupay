<?php

namespace LidLike\BluPay\DTO;

class TokenResponse
{
    public function __construct(
        public bool $success,
        public ?string $token = null,
        public ?string $errorCode = null,
        public ?string $errorDesc = null,
        public ?string $ipgUrlFromHeader = null
    ) {}
}
