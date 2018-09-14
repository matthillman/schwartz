<?php

namespace App\Parsers\SH;

use App\ModUser;
use Carbon\Carbon;

/*
"UMBARANSTARFIGHTER" => [
    [
    "starLevel" => 7,
    "level" => 85,
    "gearLevel" => 1,
    "gear" => [],
    "zetas" => [],
    "type" => "SHIP",
    "mods" => [ …6],
    "gp" => 32649,
    ],
],
"WAMPA" => [
    [
    "starLevel" => 7,
    "level" => 85,
    "gearLevel" => 12,
    "gear" => [ …2],
    "zetas" => [],
    "type" => "CHARACTER",
    "mods" => [ …6],
    "gp" => 18273,
    ],
],
*/

class ProfileParser {

    protected $user;
    protected $help;
    protected $allyCode;

    public function __construct($allyCode) {
        $this->allyCode = $allyCode;
        $this->help = new SWGOHHelp;
    }

    public function scrape() {
        $result = $this->help->getPlayer($this->allyCode);

        $this->user = $result->map(function($json) {
            $stats = [];
            collect($json['stats'])->sortBy('index')->each(function($stat) use (&$stats) {
                $stats[$stat['nameKey']] = $stat['value'];
            });

            $json['stats'] = $stats;
            $json['updated'] = Carbon::createFromTimestamp($json['updated']);

            return $json;
        })
        ->first();

        return $this->user['updated'];
    }

    public function getUser() { return $this->user; }

    public function hasChanges() {
        $user = ModUser::where('name', $this->allyCode)->first();
        return is_null($user) || $this->user['updated']->greaterThan($user->last_scrape);
    }

    public function upToDate() {
        return !$this->hasChanges();
    }

}