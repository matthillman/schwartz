<?php

namespace App;

use DB;
use ScoutElastic\Searchable;
use Illuminate\Database\Eloquent\Model;

class Guild extends Model
{
    use Searchable;
    use Util\MetaChars;

    protected $fillable = ['guild_id'];

    protected $appends = [];

    protected $indexConfigurator = Search\Indexes\GuildIndexConfigurator::class;

    protected $searchRules = [
        Search\Rules\WildcardSearchRule::class,
    ];

    protected $mapping = [
        'properties' => [
            'guild_id' => [
                'type' => 'text',
                'fields' => [
                    'raw' => [
                        'type' => 'keyword',
                    ],
                ]
            ],
            'name' => [
                'type' => 'text',
                'fields' => [
                    'raw' => [
                        'type' => 'keyword',
                    ],
                    'english' => [
                      'type' => 'text',
                      'analyzer' => 'english',
                    ],
                ]
            ],
        ]
    ];

    public function members() {
        return $this->hasMany(Member::class);
    }

    public static function getCompareData($guild1, $guild2) {
        $chars = static::getCompareCharacters();

        $data = DB::table('guild_unit_counts')
            ->whereIn('guild_id', [$guild1->guild_id, $guild2->guild_id])
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

        $mods = DB::table('guild_mod_counts')
            ->whereIn('guild_id', [$guild1->guild_id, $guild2->guild_id])
            ->get();

        $g1Mods = (array)$mods->firstWhere('guild_id', $guild1->guild_id);
        $g2Mods = (array)$mods->firstWhere('guild_id', $guild2->guild_id);

        foreach ($g1Mods as $key => $count) {
            $g1Data['mods_' . $key] = $count;
        }
        foreach ($g2Mods as $key => $count) {
            $g2Data['mods_' . $key] = $count;
        }

        $g1Data['name'] = $guild1->name;
        $g2Data['name'] = $guild2->name;

        return collect([$guild1->guild_id => $g1Data, $guild2->guild_id => $g2Data]);
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
