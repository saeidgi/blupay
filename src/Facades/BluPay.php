<?php

namespace LidLike\BluPay\Facades;

use Illuminate\Support\Facades\Facade;

class BluPay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'blupay';
    }
}
