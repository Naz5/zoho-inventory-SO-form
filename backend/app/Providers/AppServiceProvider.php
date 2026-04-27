<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\DTOs\SalesOrderDTO::class, function ($app) {
            return \App\DTOs\SalesOrderDTO::fromRequest($app['request']->all());
        });

        $this->app->bind(\App\DTOs\CustomerDTO::class, function ($app) {
            return \App\DTOs\CustomerDTO::fromRequest($app['request']->all());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
