<?php

namespace LidLike\BluPay;

use LidLike\BluPay\Drivers\BluPayDriver;

class BluPayManager
{
    public function __construct(private array $config)
    {
    }

    public function driver(): BluPayDriver
    {
        return new BluPayDriver($this->config);
    }
}
