<?php

namespace App\Providers;

use App\Services\Banner\CostCalculator;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }

    public function register(): void
    {
        $this->app->singleton(CostCalculator::class, function (Application $app) {
            // берем из папки config файл banner.php
            $config = $app->make('config')->get('banner');
            // берем из конфигурации banner цену
            return new CostCalculator($config['price']);
        });
    }
}
