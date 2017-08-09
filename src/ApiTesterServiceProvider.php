<?php

namespace Encore\Admin\ApiTester;

use Illuminate\Support\ServiceProvider;

class ApiTesterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'api-tester');

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [__DIR__.'/../resources/assets/' => public_path('vendor/api-tester')],
                'api-tester'
            );
        }

        ApiTester::boot();
    }
}