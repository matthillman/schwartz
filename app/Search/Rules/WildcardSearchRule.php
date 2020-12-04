<?php

namespace App\Search\Rules;

use ScoutElastic\SearchRule;

class WildcardSearchRule extends SearchRule
{
    /**
     * @inheritdoc
     */
    public function buildHighlightPayload()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function buildQueryPayload()
    {
        return [
            'must' => [
                'query_string' => [
                    'query' => "*{$this->builder->query}*",
                ],
            ],
        ];
    }
}