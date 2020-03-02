<?php

namespace App\Util\API;

class GuildParser {

    public $data;
    public $rawData;
    protected $useAsAllyCode;
    protected $guild;
    protected $gp;
    protected $gpMap;
    protected $zetaMap;
    protected $name;
    protected $url = '';

    public function __construct($guildOrAllyCode, $isAllyCode = false) {
        $this->url = "https://swgoh.gg/g/${guildOrAllyCode}/guild/";
        $this->guild = $guildOrAllyCode;
        $this->useAsAllyCode = $isAllyCode;
        $this->gpMap = [];
        $this->zetaMap = [];
    }

    public function scrape(Callable $memberCallback = null) {
        if ($this->useAsAllyCode) {
            $anAllyCode = $this->guild;
        } else {
            $response = guzzle()->get($this->url, ['allow_redirects' => [ 'track_redirects' => true ]]);
            $this->url = head($response->getHeader(config('redirect.history.header')));
            $anAllyCode = $this->getAnAllyCode();
        }

        $this->rawData = shitty_bot()->getGuild($anAllyCode)->first();
        $this->data = $this->rawData['profile'];
        unset($this->rawData['profile']);

        if (!is_null($memberCallback)) {
            foreach ($this->rawData['memberList'] as $member) {
                $player = shitty_bot()->getPlayer($member['playerId']);
                $player['memberLevel'] = $member['memberLevel'];
                call_user_func($memberCallback, $player);
            }
        }

        return $this;
    }

    protected function getAnAllyCode() {
        $page = goutte()->request('GET', $this->url);
        $slug = $page->filter('table tbody tr td:first-child a')->attr('href');
        return (preg_match('/\/(\d+)\/$/', $slug, $matches)) ? trim($matches[1]) : null;
    }

    public function name() {
        return $this->data['name'];
    }
    public function gp() {
        return $this->data['guildGalacticPower'];
    }
    public function members() {
        return collect($this->rawData['memberList']);
    }
    public function url() {
        return $this->url;
    }
}