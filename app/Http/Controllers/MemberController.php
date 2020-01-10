<?php

namespace App\Http\Controllers;

use App\Unit;
use App\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    use Util\Squads;

    public function show($allyCode) {
        $member = Member::with('characters.zetas')->where('ally_code', $allyCode)->firstOrFail();

        return view('member.profile', [
            'member' => $member,
            'units' => Unit::all(),
        ]);
    }

    public function listTeams($allyCode, $team) {
        $member = Member::with('characters.zetas')->where('ally_code', $allyCode)->firstOrFail();

        list($highlight, $teams) = $this->getSquadsFor($team);

        return view("member.teams", [
            'member' => $member,
            'units' => Unit::all(),
            'teams' => $teams,
            'highlight' => $highlight,
            'team' => $team,
        ]);
    }
}
