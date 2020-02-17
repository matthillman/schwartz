<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BootstrapElasticsearch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call('elastic:create-index', [ 'index-configurator' => 'App\Search\Indexes\GuildIndexConfigurator' ]);
        Artisan::call('elastic:create-index', [ 'index-configurator' =>'App\Search\Indexes\UnitIndexConfigurator' ]);
        Artisan::call('elastic:create-index', [ 'index-configurator' =>'App\Search\Indexes\MemberIndexConfigurator' ]);
        Artisan::call('elastic:update-mapping', [ 'model' => 'App\Guild' ]);
        Artisan::call('elastic:update-mapping', [ 'model' => 'App\Unit' ]);
        Artisan::call('elastic:update-mapping', [ 'model' => 'App\Member' ]);
        Artisan::call('scout:import', [ 'model' => 'App\Guild' ]);
        Artisan::call('scout:import', [ 'model' => 'App\Unit' ]);
        Artisan::call('scout:import', [ 'model' => 'App\Member' ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
