<?php

namespace App\Http\Controllers;

use App\Guild;
use App\Member;
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

    public function getAllyFromDiscord(Request $request, $id) {
        return response()->json(swgoh()->registration([$id]));
    }

    public function register(Request $request, $id, $discord) {
        return response()->json(swgoh()->registration([], [[$id, $discord]]));
    }

    public function deleteRegistration(Request $request, $id) {
        return response()->json(swgoh()->registration([], [], [$id]));
    }
}
