<?php

namespace App\Mods\Concerns;

trait ParsesRegex {
    protected static function getStringValue($text, $regex) {
        if (preg_match($regex, $text, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    protected static function getIntegerValue($text, $regex) {
        if (preg_match($regex, $text, $matches)) {
            return intval(trim($matches[1])) - 1;
        }

        return null;
    }
}