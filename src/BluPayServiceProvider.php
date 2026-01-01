<?php

namespace LidLike\BluPay;

use Illuminate\Support\ServiceProvider;

class BluPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/blupay.php', 'blupay');

        $this->app->singleton('blupay', function () {
            return new BluPayManager(config('blupay'));
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/blupay.php' => config_path('blupay.php'),
        ], 'blupay-config');
    }
}
