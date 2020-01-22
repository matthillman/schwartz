<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class Guild extends Model
{
    protected $fillable = ['guild_id'];

    protected $appends = [];

    public function members() {
        return $this->hasMany(Member::class);
    }

    public static function getCompareCharacters() {
        return [
            'GENERALSKYWALKER' =>    'General Skywalker',
            'DARTHREVAN' =>          'Darth Revan',
            'DARTHMALAK' =>          'Malak',
            'JEDIKNIGHTREVAN' =>     'Revan',
            'PADMEAMIDALA' =>        'Padmé',
            'GRIEVOUS' =>            'Grievous',
            'GEONOSIANBROODALPHA' => 'Geo Alpha',
            'DARTHTRAYA' =>          'Traya',
            'ANAKINKNIGHT'        => 'Anakin',
        ];;
    }

    public static function getCompareData($guild1, $guild2) {
        $chars = static::getCompareCharacters();

        $unitQueries = collect($chars)->map(function($name, $unitName) {
            return [
                "sum(case when characters.unit_name = '${unitName}' then 1 else 0 end) as ${unitName}",
                "sum(case when characters.unit_name = '${unitName}' AND characters.gear_level = 11 then 1 else 0 end) as ${unitName}_11",
                "sum(case when characters.unit_name = '${unitName}' AND characters.gear_level = 12 then 1 else 0 end) as ${unitName}_12",
                "sum(case when characters.unit_name = '${unitName}' AND characters.gear_level = 13 then 1 else 0 end) as ${unitName}_13",
                "sum(case when characters.unit_name = '${unitName}' AND characters.relic > 5 then 1 else 0 end) as ${unitName}_r_total",
                "sum(case when characters.unit_name = '${unitName}' AND characters.relic = 7 then 1 else 0 end) as ${unitName}_r5",
                "sum(case when characters.unit_name = '${unitName}' AND characters.relic = 8 then 1 else 0 end) as ${unitName}_r6",
                "sum(case when characters.unit_name = '${unitName}' AND characters.relic = 9 then 1 else 0 end) as ${unitName}_r7",
            ];
        })->collapse()->implode(', '); // FIXME ->join when upgrading laravel

        $data = DB::table('guilds') ->join('members', 'members.guild_id', '=', 'guilds.id') ->join('characters', 'characters.member_id', '=', 'members.id') ->selectRaw("
                guilds.guild_id,
                max(guilds.gp) as gp,
                count(distinct members.id) as member_count,
                sum(case when characters.gear_level = 13 then 1 else 0 end) as gear_13,
                sum(case when characters.gear_level = 12 then 1 else 0 end) as gear_12,
                sum(case when characters.gear_level = 11 then 1 else 0 end) as gear_11,
                sum(case when characters.relic = 9 then 1 else 0 end) as relic_7,
                sum(case when characters.relic = 8 then 1 else 0 end) as relic_6,
                sum(case when characters.relic = 7 then 1 else 0 end) as relic_5,
                ${unitQueries}
            ") ->groupBy('guilds.guild_id')
            ->whereIn('guilds.guild_id', [$guild1->guild_id, $guild2->guild_id])
            ->get();

        $g1Data = (array)$data->firstWhere('guild_id', $guild1->guild_id);
        $g2Data = (array)$data->firstWhere('guild_id', $guild2->guild_id);

        $zetas = DB::table('character_zeta')
            ->join('characters', 'character_id', '=', 'characters.id')
            ->join('members', 'characters.member_id', '=', 'members.id')
            ->selectRaw('count(1) as zetas, members.guild_id')
            ->groupBy('members.guild_id')
            ->whereIn('members.guild_id', [$guild1->id, $guild2->id])
            ->get();

        $g1Zetas = (array)$zetas->firstWhere('guild_id', $guild1->id);
        $g2Zetas = (array)$zetas->firstWhere('guild_id', $guild2->id);

        $g1Data['zetas'] = $g1Zetas['zetas'];
        $g2Data['zetas'] = $g2Zetas['zetas'];

        $mods = DB::table('guilds')
            ->join('members', 'members.guild_id', '=', 'guilds.id')
            ->join('mod_users', 'mod_users.name', '=', 'members.ally_code')
            ->join('mod_stats', 'mod_stats.mod_user_id', '=', 'mod_users.id')
            ->selectRaw("
                guilds.guild_id,
                sum(case when pips = 6 then 1 else 0 end) as six_dot,
                sum(case when speed >= 10 then 1 else 0 end) as ten_plus,
                sum(case when speed >= 15 then 1 else 0 end) as fifteen_plus,
                sum(case when speed >= 20 then 1 else 0 end) as twenty_plus,
                sum(case when speed >= 25 then 1 else 0 end) as twenty_five_plus,
                sum(case when offense >= 100 then 1 else 0 end) as one_hundred_offense
            ") ->groupBy('guilds.guild_id')
            ->whereIn('guilds.guild_id', [$guild1->guild_id, $guild2->guild_id])
            ->get();

        $g1Mods = (array)$mods->firstWhere('guild_id', $guild1->guild_id);
        $g2Mods = (array)$mods->firstWhere('guild_id', $guild2->guild_id);

        $g1Data['mods'] = $g1Mods;
        $g2Data['mods'] = $g2Mods;

        return collect([$guild1->id => $g1Data, $guild2->id => $g2Data]);
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
}
