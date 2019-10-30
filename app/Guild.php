<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class Guild extends Model
{
    protected $fillable = ['guild_id'];

    protected $appends = ['icon_name', 'raid_tag'];

    public function members() {
        return $this->hasMany(Member::class);
    }

    public function getCompareDataAttribute() {
        return DB::table('guilds')
            ->join('members', 'members.guild_id', '=', 'guilds.id')
            ->join('characters', 'characters.member_id', '=', 'members.id')
            ->selectRaw("
                guilds.guild_id,
                sum(case when characters.gear_level = 12 then 1 else 0 end) as gear_12,
                sum(case when characters.gear_level = 11 then 1 else 0 end) as gear_11,
                sum(case when characters.unit_name = 'DARTHTRAYA' then 1 else 0 end) as traya,
                sum(case when characters.unit_name = 'JEDIKNIGHTREVAN' then 1 else 0 end) as revan
            ")
            ->groupBy('guilds.guild_id')
            ->whereIn('guilds.guild_id', [$this->guild_id])
            ->get();
    }

    public function getModDataAttribute() {
        return DB::table('guilds')
            ->join('members', 'members.guild_id', '=', 'guilds.id')
            ->join('mod_users', 'mod_users.name', '=', 'members.ally_code')
            ->join('mod_stats', 'mod_stats.mod_user_id', '=', 'mod_users.id')
            ->selectRaw("
                members.id,
                members.player,
                members.url,
                members.ally_code,
                sum(case when pips = 6 then 1 else 0 end) as six_dot,
                sum(case when speed >= 10 then 1 else 0 end) as speed_10,
                sum(case when speed >= 15 then 1 else 0 end) as speed_15,
                sum(case when speed >= 20 then 1 else 0 end) as speed_20,
                sum(case when speed >= 25 then 1 else 0 end) as speed_25,
                sum(case when offense >= 100 then 1 else 0 end) as offense_100
            ")
            ->groupBy('members.id', 'members.player', 'members.url', 'members.ally_code')
            ->whereIn('guilds.guild_id', [$this->guild_id])
            ->get();
    }

    public function getIconNameAttribute() {
        switch ($this->guild_id) {
            case 3577:
                return 'mandalorian';
                break;

            case 2238:
                return 'sabine';
                break;

            case 29865:
                return 'senate';
                break;

            case 42367:
                return 'triangle';
                break;

            case 11339:
                return 'senate';
                break;

            case 30376:
                return 'niteowl';
                break;

            case 8545:
                return 'blast';
                break;

            case 40305:
                return 'blacksun';
                break;

            case 48168:
                return 'wolffe';
                break;

            default:
                return '';
                break;
        }
    }
    public function getRaidTagAttribute() {
        switch ($this->guild_id) {
            case 3577:  // ROTS
            case 2238:  // TS
            case 29865: // SHS
            case 42367: // TSSB
            case 11339: // TPS
                return 'Heroic Sith';
                break;


            case 30376: // TCS
            case 8545:  // ANS
            case 40305: // BSS
            case 48168: // ASWS
                return 'Heroic Tank';
                break;

            default:
                return '';
                break;
        }
    }
}
