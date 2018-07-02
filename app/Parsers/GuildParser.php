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
        $body = (string)$response->getBody();
        $this->gp = $this->getGPFrom($body);

        $head = static::getStringValue($body, '/h1 class=".*?h1.*?">\s*(.*?)\s*<\/h1>/s');
        $this->name = static::getStringValue($head, '/<br.*?>\s*(.+?)\s*<br.*?>/');
        $this->name = str_replace('&#39;', "'", $this->name);

        $this->scrapeGuildGP();
        $this->scrapeZetas();

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

    protected function scrapeZetas() {
        $page = goutte()->request('GET', "{$this->url}zetas/");

        $page->filter('table tbody tr')->each(function($row) {
            $slug = $row->filter('td:nth-child(1) a')->attr('href');

            $zetaMap = [];

            $row->filter('.guild-member-zeta')->each(function($zeta) use (&$zetaMap) {
                $char = $zeta->filter('.char-portrait')->attr('title');
                str_replace('&#39;', "'", $char);
                $unit = Unit::where(['name' => $char])->firstOrFail();

                $zetaMap[$unit->base_id] = [];

                $zeta->filter('.guild-member-zeta-ability')->each(function($z) use ($unit, &$zetaMap) {
                    $name = $z->attr('title');
                    $zetaMap[$unit->base_id][] = $name;
                });
            });

            $this->zetaMap[$slug . 'collection/'] = $zetaMap;
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
    public function zetas() {
        return $this->zetaMap;
    }
    public function url() {
        return $this->url;
    }

    private function getGPFrom($html) {
        return static::getIntegerValue($html, '/class="stat-item-value">(?<value>[^<]+)/m');
    }
}