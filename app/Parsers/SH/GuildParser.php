<?php

namespace App\Parsers;

use App\Unit;
use App\Guild;

class GuildParser {

    use Concerns\ParsesRegex;

    protected $guild;
    protected $gp;
    protected $gpMap;
    protected $zetaMap;
    protected $name;
    protected $url = '';

    public function __construct($guild) {
        $this->url = "https://swgoh.gg/g/${guild}/guild/";
        $this->guild = $guild;
        $this->gpMap = [];
        $this->zetaMap = [];
    }

    public function scrape() {
        $response = guzzle()->get($this->url, ['allow_redirects' => [ 'track_redirects' => true ]]);
        $this->url = head($response->getHeader(config('redirect.history.header')));
        $anAllyCode = $this->getAnAllyCode();





        return $this;
    }

    protected function getAnAllyCode() {
        $page = goutte()->request('GET', $this->url);
        $slug = $page->filter('table tbody tr td:first-child a')->attr('href');
        return (preg_match('/\/^(.+)\/$/', $slug, $matches)) ? trim($matches[1]) : null;

            // $this->gpMap[$slug . 'collection/'] = [
            //     'gp' => $gp,
            //     'character_gp' => $charGP,
            //     'ship_gp' => $shipGP,
            // ];
    }

    public function name() {
        return $this->name;
    }
    public function gp() {
        return $this->gp;
    }
    public function memberGP() {
        return $this->gpMap;
    }
    public function zetas() {
        return $this->zetaMap;
    }
    public function url() {
        return $this->url;
    }
}