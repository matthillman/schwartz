<?php

namespace App\Http\Controllers;

use App\Unit;
use App\Category;
use Illuminate\Http\Request;

class MetadataController extends Controller
{
    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function units()
    {
        return view('units');
    }
    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function categories()
    {
        return view('categories');
    }

    public function ggUser($user, $param) {
        return redirect()->away("https://swgoh.gg/u/$user/$param");
    }
    public function ggPlayer($user, $param) {
        return redirect()->away("https://swgoh.gg/p/$user/$param");
    }

    public function podcastRSS() {
        return redirect()->away("http://feeds.soundcloud.com/users/soundcloud:users:536817606/sounds.rss");
    }

    public function discordWidget() {
        $response = guzzle()->get("https://discordapp.com/api/guilds/${id}/widget.json");
        return $response->getBody();
    }
}
