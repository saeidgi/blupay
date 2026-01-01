<?php

namespace LidLike\BluPay\DTO;

class VerifyResponse
{
    public function __construct(
        public bool $success,
        public ?int $resultCode = null,
        public ?string $resultDescription = null,
        public array $transactionDetail = []
    ) {}
}
