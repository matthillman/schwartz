<?php

namespace App\Http\Controllers;

use App\User;
use App\Guild;
use App\Member;
use App\AllyCodeMap;
use App\DiscordRole;
use App\TerritoryWarPlan;
use App\Jobs\ProcessUser;
use App\Jobs\ProcessGuild;
use Illuminate\Http\Request;
use App\Jobs\ProcessGuildAlly;

class APIController extends Controller
{
    public function ping() {
        return response()->json(['pong' => true]);
    }

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
        $existing = User::firstOrNew(['discord_id' => $id])->allyCodeForGuild($server);
        if (is_null($existing)) {
            $server = null;
        }
        $id = str_replace('-', '', $id);
        AllyCodeMap::upsert(['ally_code' => $id, 'server_id' => $server, 'discord_id' => $discord], ["discord_id", "server_id", "ally_code)"]);
        return response()->json([]);
    }

    public function deleteRegistration(Request $request, $id) {
        // return response()->json(swgoh()->registration([], [], [$id]));
    }

    public function guildQueryResponse(Request $request) {
        $members = $request->input('response');

        foreach ($members as $member) {
            $mapping = DiscordRole::firstOrNew(['discord_id' => $member['id']]);
            $mapping->username = $member['username'];
            $mapping->discriminator = $member['discriminator'];
            $currentRoles = $mapping->roles;
            $currentRoles[$member['guild']] = $member;
            $mapping->roles = $currentRoles;
            $mapping->save();
            if ($mapping->user) {
                broadcast(new \App\Events\PermissionsUpdated($mapping->user));
            }
        }

    }

    public function sendDmResponse(Request $request) {
        $discordID = $request->input('member');

        $plan = TerritoryWarPlan::findOrFail($request->input('context'));

        $role = DiscordRole::where('discord_id', $discordID)->firstOrFail();
        $role->dm_status = $request->input('success') ? DiscordRole::DM_SUCCESS : min($role->dm_status, 0) + DiscordRole::DM_FAILED;
        $role->save();

        if ($role->dm_status < 0 && abs($role->dm_status) < 5) {
            broadcast(new \App\Events\BotCommand([
                'command' => 'send-dms',
                'members' => [[ 'ally_code' => $role->ally->ally_code, 'id' => $role->discord_id ]],
                'url' => "twp/{$plan->id}/member",
                'message' => 'Here are your defensive assignments for this TW! Please ask if you have any questions!',
                'tag' => ['dm', "plan:{$plan->id}"],
                'context' => "$plan->id",
            ]));
        }

        broadcast(new \App\Events\DMState($plan, ['ally_code' => $role->ally->ally_code, 'dm_status' => $role->dm_status]));
    }
}
