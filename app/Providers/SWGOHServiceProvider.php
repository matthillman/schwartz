<?php

namespace App\Providers;

use App\Parsers\SH\SWGOHHelp;
use Illuminate\Support\ServiceProvider;

class SWGOHServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('swgoh', function () {
            return new SWGOHHelp;
        });
    }
}
