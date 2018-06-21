<?php

namespace App\Providers;

use Goutte\Client;
use Illuminate\Support\ServiceProvider;

class GoutteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('goutte', function () {
            return new Client;
        });
    }
}
