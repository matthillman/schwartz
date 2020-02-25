<?php

namespace App\Http\Controllers;

use App\User;
use App\Guild;
use App\Member;
use App\AllyCodeMap;
use App\Jobs\ProcessUser;
use App\Jobs\ProcessGuild;
use Illuminate\Http\Request;
use App\Jobs\ProcessGuildAlly;

class APIController extends Controller
{
    public function scrapeGuild(Request $request, $id) {
        $isAllyCode = preg_match('/^\d{3}-?\d{3}-?\d{3}$/', $id);
        if ($isAllyCode) {
            $id = preg_replace('/[^0-9]/', '', $id);
            ProcessGuildAlly::dispatch($id);
        } else {
            ProcessGuild::dispatch($id);
        }
        return response()->json([]);
    }

    public function scrapeMember(Request $request, $id) {
        ProcessUser::dispatch($id);
        return response()->json([]);
    }

    public function whois($id) {
        return response()->json(AllyCodeMap::where(['discord_id' => $id])->get());
    }

    public function getAllyFromDiscord(Request $request, $id, $server = null) {
        $user = User::where(['discord_id' => $id])->first();
        $allyCode = null;
        if (is_null($user)) {
            $server = AllyCodeMap::where(['discord_id' => $id, 'server_id' => $server])->first();

            if (is_null($server)) {
                $server = AllyCodeMap::where(['discord_id' => $id])->whereNull('server_id')->first();
            }

            if (!is_null($server)) {
                $allyCode = $server->ally_code;
            }
        } else {
            $allyCode = $user->allyCodeForGuild($server);
        }
        if (!is_null($allyCode)) {
            return response()->json([
                'get' => [
                    ['allyCode' => $allyCode, 'discordId' => $id]
                ]
            ]);
        }
        $info = swgoh()->registration([$id]);
        AllyCodeMap::create(['discord_id' => $id, 'server_id' => $server, 'ally_code' => $info['get'][0]['allyCode']]);
        return response()->json($info);
    }

    public function register(Request $request, $id, $discord, $server = null) {
        $existing = User::where(['discord_id' => $id])->allyCodeForGuild($server);
        if (is_null($existing)) {
            $server = null;
        }
        AllyCodeMap::upsert(['ally_code' => $id, 'server_id' => $server, 'discord_id' => $discord], "(discord_id, server_id, ally_code)");
        return response()->json([]);
    }

    public function deleteRegistration(Request $request, $id) {
        // return response()->json(swgoh()->registration([], [], [$id]));
    }
}
