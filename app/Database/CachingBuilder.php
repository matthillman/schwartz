<?php

namespace App\Database;

use Cache;
use Illuminate\Database\Query\Builder as QueryBuilder;

class CachingBuilder extends QueryBuilder
{
    /**
     * Run the query as a "select" statement against the connection.
     *
     * @return array
     */
    protected function runSelect()
    {
        return Cache::store('game-data')->remember($this->getCacheKey(), null, function() {
            return parent::runSelect();
        });
    }

    /**
     * Returns a Unique String that can identify this Query.
     *
     * @return string
     */
    protected function getCacheKey()
    {
        return json_encode([
            $this->toSql() => $this->getBindings()
        ]);
    }
}