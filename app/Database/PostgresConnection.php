<?php

namespace App\Database;

use DateTimeInterface;
use Staudenmeir\LaravelCte\Connections\PostgresConnection as BaseConnection;

class PostgresConnection extends BaseConnection {

    // Copied from Connection, overridden to cast boolean values to strings instead of ints
    // so postgres stops complaining
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            // We need to transform all instances of DateTimeInterface into the actual
            // date string. Each query grammar maintains its own date string format
            // so we'll just ask the grammar for the format to get from the date.
            if ($value instanceof DateTimeInterface || is_a($value, 'DateTimeInterface')) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = (string) (int) $value;
            }
        }

        return $bindings;
    }
}