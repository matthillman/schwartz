<?php

namespace App\Parsers;

use App\Guild;

class GuildParser {

    use Concerns\ParsesRegex;

    protected $guild;
    protected $gp;
    protected $name;
    protected $url = '';

    public function __construct($guild) {
        $this->url = "https://swgoh.gg/g/${guild}/guild/";
        $this->guild = $guild;
    }

    public function scrape() {
        $response = guzzle()->get($this->url, ['allow_redirects' => [ 'track_redirects' => true ]]);
        $this->url = head($response->getHeader(config('redirect.history.header')));
        $body = (string)$response->getBody();
        $this->gp = $this->getGPFrom($body);

        $head = static::getStringValue($body, '/h1 class=".*?h1.*?">\s*(.*?)\s*<\/h1>/s');
        $this->name = static::getStringValue($head, '/<br.*?>\s*(.+?)\s*<br.*?>/');

        return $this;
    }

    public function name() {
        return $this->name;
    }
    public function gp() {
        return $this->gp;
    }
    public function url() {
        return $this->url;
    }

    private function getGPFrom($html) {
        return static::getIntegerValue($html, '/class="stat-item-value">(?<value>[^<]+)/m');
    }
}