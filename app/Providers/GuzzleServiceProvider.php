<?php

namespace App\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\RedirectMiddleware;
use Illuminate\Support\ServiceProvider;

class GuzzleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('guzzle', function () {
            $config = isset($this->app['config']['guzzle']) ? $this->app['config']['guzzle'] : [];
            return new Client($config);
        });

        config([
            'redirect.history.header' => RedirectMiddleware::HISTORY_HEADER
        ]);
    }
}
