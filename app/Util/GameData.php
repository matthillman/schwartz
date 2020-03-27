<?php

namespace App\Util;

use Cache;
use Storage;

class GameData {

    public static function skills() {
        return static::getCached('skillList.json');
    }
    public static function recipes() {
        return static::getCached('recipeList.json');
    }
    public static function materials() {
        return static::getCached('materialList.json');
    }

    private static function getCached($file) {
        return Cache::store('game-data')->remember($file, null, function() use ($file) {
            return collect(json_decode(Storage::disk('game_data')->get($file), true))->keyBy('id');
        });
    }

    public static function parseGameData($fileName, Callable $callback) {
        $stream = Storage::disk('game_data')->readStream($fileName);

        try {
            $listener = new \SwgohHelp\GuildListener($callback);
            $parser = new \JsonStreamingParser\Parser($stream, $listener);
            $parser->parse();
        } finally {
            fclose($stream);
        }
    }

    public static function test() {
        static::parseGameData('unitsList.json', function($data) {
            if (isset($data['baseId'])) {
                collect($data)->dd();
                return true;
            }
            return false;
        });
    }

}