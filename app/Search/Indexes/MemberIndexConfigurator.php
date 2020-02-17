<?php

namespace App\Search\Indexes;

use ScoutElastic\IndexConfigurator;
use ScoutElastic\Migratable;

class MemberIndexConfigurator extends IndexConfigurator
{
    use Migratable;

    /**
     * @var array
     */
    protected $settings = [
        //
    ];
}