<?php

namespace App\Http\Controllers\Util;

trait UpdatesRoles {
    private function doRoleUpdate($user = null, $fullGuild = false) {
        $user = $user ?: auth()->user();
        $guilds = $user->accounts
            ->filter(function ($m) {
                return $m->guild && !is_null($m->guild->server_id);
            })
            ->map(function ($m) use ($user, $fullGuild) {
                if ($fullGuild) {
                    $members = $m->guild->members->pluck('discord')->whereNotNull('discord_id')->pluck('discord_id');
                } else {
                    $members = $user->discord_id;
                }
                return [
                    'guild' => $m->guild->server_id,
                    'member' => $members,
                ];
            });
        if (count($guilds) === 0) {
            return redirect()->intended('home');
        }

        broadcast(new \App\Events\BotCommand([
            'command' => 'guild-query',
            'guilds' => $guilds,
        ]));

        return redirect()->route('waiting.roles');
    }

    private function updateMemberList($user = null) {
        $user = $user ?: auth()->user();

        $guilds = $user->accounts
            ->filter(function ($m) {
                return $m->guild && !is_null($m->guild->server_id);
            })
            ->map(function ($m) {
                return [
                    'guild' => $m->guild->server_id,
                    'role' => $m->guild->member_role_regex,
                ];
            });
        if (count($guilds) === 0) {
            return redirect()->intended('home');
        }

        broadcast(new \App\Events\BotCommand([
            'command' => 'guild-query',
            'guilds' => $guilds,
        ]));

        return redirect()->route('waiting.roles');
    }
}