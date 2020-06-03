<?php

namespace App\Util;

// FIXME this can be a standalone class

use Storage;
use JsonStreamingParser\Listener\SubsetConsumerListener;

class JsonObjectConsumer extends SubsetConsumerListener {

    private $callback;

    public function __construct (Callable $callback) {
        $this->callback = $callback;
    }

    protected function consume($data) {
        call_user_func($this->callback, $data);
    }

    public static function parseGameData($fileName, Callable $callback) {
        $stream = Storage::disk('game_data')->readStream($fileName);

        try {
            $listener = new \SwgohHelp\Listeners\GuildListener($callback);
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