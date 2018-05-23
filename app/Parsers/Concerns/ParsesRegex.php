<?php

namespace App\Parsers\Concerns;

trait ParsesRegex {
    protected static function getStringValue($text, $regex) {
        if (preg_match($regex, $text, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    protected static function getIntegerValue($text, $regex) {
        if (preg_match($regex, $text, $matches)) {
            return intval(trim(str_replace(',', '', $matches[1])));
        }

        return null;
    }

    protected static function getArrayIndexValue($text, $regex) {
        $intval = static::getIntegerValue($text, $regex);
        return $intval ? $intval - 1 : null;
    }
}