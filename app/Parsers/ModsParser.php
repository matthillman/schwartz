<?php

namespace App\Parsers;

class ModsParser {

    use Concerns\ParsesRegex;

    protected $page = 1;
    public $mods;
    protected $url = '';

    public function __construct($user) {
        $this->url = "https://swgoh.gg/u/${user}/";
        $this->mods = collect([]);
    }

    public function scrape() {
        do {
            $response = guzzle()->get($this->currentURL());
            $body = (string)$response->getBody();
            $this->mods = $this->mods->merge($this->scrapeCurrentPage($body));
        } while ($this->setNextPageFrom($body));
    }

    private function scrapeCurrentPage($html) {
        $parts = collect(preg_split('/<div\s+class="col-xs-12[^>]+>/m', $html));
        $parts->shift();
        return $parts->transform(function($mod) {
            return $this->parseModHTML($mod);
        });
    }

    private function currentURL() {
        return "{$this->url}mods/?page={$this->page}";
    }

    private function setNextPageFrom($body) {
        if (preg_match('/<a\s+href="[^?]+\/mods\/\?page=([0-9]+)"\s+aria-label="Next"/m', $body, $matches)) {
            $this->page = $matches[1];
            return true;
        }

        return false;
    }

    private function parseModHTML($html) {
        $mod = [];

        $mod['uid'] = $this->getUID($html);
        $mod['slot'] = $this->getSlot($html);
        $mod['set'] = $this->getSet($html);
        $mod['pips'] = $this->getPips($html);
        $mod['level'] = $this->getLevel($html);
        $mod['tier'] = $this->getTier($html);

        $stats = $this->getStats($html);
        $mod['primary'] = array_shift($stats);
        $mod['secondaries'] = [];
        foreach ($stats as $stat) {
            $mod['secondaries'][$stat['type']] = $stat['value'];
        }

        $names = $this->getNames($html);
        $mod['name'] = $names['name'];
        $mod['location'] = $names['location'];

        return $mod;
    }

    private function getUID($el) {
        return static::getStringValue($el, '/data-id="([^"]+)/m');
    }

    private function getSlot($el) {
        $slot = static::getArrayIndexValue($el, '/tex\.statmodmystery_[0-9]+_([0-9]+)/m');
        return is_null($slot) ? null : ['square', 'arrow', 'diamond', 'triangle', 'circle', 'cross'][$slot];
    }

    private function getSet($el) {
        $set = static::getArrayIndexValue($el, '/tex\.statmodmystery_([0-9]+)_[0-9]+/m');
        return is_null($set) ? null : ['health', 'offense', 'defense', 'speed', 'critchance', 'critdamage', 'potency', 'tenacity'][$set];
    }

    private function getPips($el) {
        return preg_match_all('/("statmod-pip")/m', $el) ?: 0;
    }

    private function getLevel($el) {
        return static::getStringValue($el, '/statmod-level">([0-9]+)/m');
    }

    private function getTier($el) {
        return static::getIntegerValue($el, '/statmod-t([0-9])/m');
    }

    private function getStats($el) {
        $count = preg_match_all('/class="statmod-stat-value">(?<value>[^<]+)<\/span>\s*<span\s+class="statmod-stat-label">(?<type>[^<]+)/m', $el, $matches);
        $stats = [];
        for ($i=0; $i < $count; $i++) {
            $type = strtolower($matches['type'][$i]);
            $value = $matches['value'][$i];
            $special = ['defense', 'health', 'offense', 'protection'];

            if ($i > 0 && in_array($type, $special) && str_contains($value, "%")) {
                $value = str_replace("%", "", $value);
                $type .= " %";
            }
            $stats[] = [
                'type' => $type,
                'value' => $value,
            ];
        }

        return $stats;
    }

    private function getNames($el) {
        $count = preg_match_all('/png"\s+alt="(?<name>[^"]+)/m', $el, $matches);
        if (!$count) {
            return [
                'name' => null,
                'location' => null,
            ];
        }

        return [
            'name' => $matches['name'][1],
            'location' => str_replace("&quot;", '"', $matches['name'][0]),
        ];
    }
}