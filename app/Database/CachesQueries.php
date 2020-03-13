<?php

namespace App\Database;

trait CachesQueries
{
    /**
     * Get a new query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

        return new CachingBuilder($conn, $grammar, $conn->getPostProcessor());
    }
}