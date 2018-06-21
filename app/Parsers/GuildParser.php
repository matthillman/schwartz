<?php

namespace App\Parsers;

use App\Guild;
use Goutte\Client;

class GuildParser {

    use Concerns\ParsesRegex;

    protected $guild;
    protected $gp;
    protected $gpMap;
    protected $name;
    protected $url = '';

    public function __construct($guild) {
        $this->url = "https://swgoh.gg/g/${guild}/guild/";
        $this->guild = $guild;
        $this->gpMap = [];
    }

    public function scrape() {
        $response = guzzle()->get($this->url, ['allow_redirects' => [ 'track_redirects' => true ]]);
        $this->url = head($response->getHeader(config('redirect.history.header')));
        $body = (string)$response->getBody();
        $this->gp = $this->getGPFrom($body);

        $head = static::getStringValue($body, '/h1 class=".*?h1.*?">\s*(.*?)\s*<\/h1>/s');
        $this->name = static::getStringValue($head, '/<br.*?>\s*(.+?)\s*<br.*?>/');

        $this->scrapeGuildGP();

        return $this;
    }

    protected function scrapeGuildGP() {
        $page = goutte()->request('GET', "{$this->url}gp/");

        $page->filter('table tbody tr')->each(function($row) {
            $slug = $row->filter('td:nth-child(1) a')->attr('href');
            $gp = intval($row->filter('td:nth-child(2)')->text());
            $charGP = intval($row->filter('td:nth-child(3)')->text());
            $shipGP = intval($row->filter('td:nth-child(4)')->text());

            $this->gpMap[$slug . 'collection/'] = [
                'gp' => $gp,
                'character_gp' => $charGP,
                'ship_gp' => $shipGP,
            ];
        });
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
    public function url() {
        return $this->url;
    }

    private function getGPFrom($html) {
        return static::getIntegerValue($html, '/class="stat-item-value">(?<value>[^<]+)/m');
    }
}