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

    protected $appends = [ 'server_name' ];

    protected $hidden = [ 'server' ];

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

    public function stats() {
        return $this->hasOne(GuildStat::class)->withDefault();
    }

    public function server() {
        return $this->hasOne(Server::class, 'server_id', 'server_id')->withDefault();
    }

    public function getServerNameAttribute() {
        return $this->server->name ?? '';
    }

    public function getOfficerRoleRegexAttribute($value) {
        if (testRegex($value)) {
            return $value;
        }
        return '^officer';
    }

    public function getMemberRoleRegexAttribute($value) {
        if (testRegex($value)) {
            return $value;
        }
        return '^member';
    }

    public function getDiscordMembersAttribute() {
        return collect(DB::select("select discord_id, username, discriminator, d_roles.name as role_name
            from discord_roles, jsonb_to_recordset(discord_roles.roles->?->'roles') as d_roles(name text)
            where d_roles.name ~* ?", [$this->server_id, $this->member_role_regex]));
    }

    public function discordMemberOptions() {
        return $this->discord_members->map(function ($m) {
            return ['value' => $m->discord_id, 'label' => "{$m->username}#{$m->discriminator}"];
        });
    }

    public static function getCompareData($guild1, $guild2) {
        $chars = static::getCompareCharacters();

        // $data = DB::table('guild_unit_counts')
        //     ->whereIn('guild_id', [$guild1->guild_id, $guild2->guild_id])
        //     ->get();

        // $g1Data = (array)$data->firstWhere('guild_id', $guild1->guild_id);
        // $g2Data = (array)$data->firstWhere('guild_id', $guild2->guild_id);

        $g1Stats = GuildStat::where('guild_id', $guild1->id)->firstOrFail();
        $g2Stats = GuildStat::where('guild_id', $guild2->id)->firstOrFail();

        $g1Data = $g1Stats->unit_data;
        $g2Data = $g2Stats->unit_data;

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

        // $mods = DB::table('guild_mod_counts')
        //     ->whereIn('guild_id', [$guild1->guild_id, $guild2->guild_id])
        //     ->get();

        // $g1Mods = (array)$mods->firstWhere('guild_id', $guild1->guild_id);
        // $g2Mods = (array)$mods->firstWhere('guild_id', $guild2->guild_id);

        $g1Mods = $g1Stats->mod_data;
        $g2Mods = $g2Stats->mod_data;

        foreach ($g1Mods as $key => $count) {
            $g1Data['mods_' . $key] = $count;
        }
        foreach ($g2Mods as $key => $count) {
            $g2Data['mods_' . $key] = $count;
        }

        $g1Data['name'] = $guild1->name;
        $g2Data['name'] = $guild2->name;
        $g1Data['guild_id'] = $guild1->guild_id;
        $g2Data['guild_id'] = $guild2->guild_id;

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

    public function averageMember($compareUnits = []) {
        $member = $this->members()
            ->with(['stats', 'characters.zetas'])
            ->get()
            ->map(function(Member $member) use($compareUnits) {
                return $member->toCompareData($compareUnits)->toArray();
            })
            ->reduce(function($average, $member) {
                return average_array($average, $member);
            }, [])
        ;
        $member = round_array($member);

        $member['player'] = "Average Joe";

        return collect($member)->mapWithKeys(function($val, $key) {
            if (is_array($val)) {
                $val = collect($val);
            }
            return [$key => $val];
        });
    }
}

function testRegex($regex) {
    if (is_null($regex) || strpos($regex, chr(0x00)) !== false || ! trim($regex)) {
        return false;
    }

    $backtrack_limit = ini_set('pcre.backtrack_limit', 200);
    $recursion_limit = ini_set('pcre.recursion_limit', 20);

    $valid = @preg_match("~$regex~u", null) !== false;

    ini_set('pcre.backtrack_limit', $backtrack_limit);
    ini_set('pcre.recursion_limit', $recursion_limit);

    return $valid;
}

function combine_array($average, $array) {
    foreach ($array as $key => $val) {
        // echo "Considering $key => " . json_encode($val) . " -> [" . json_encode(array_get($average, $key)) . "]\n" ;
        if (isset($average[$key])) {
            if (is_array($val)) {
                $average[$key] = average_array($average[$key], $val);
            } else if (is_string($val) || is_bool($val)) {
                $average[$key] = $val;
            } else if (is_numeric($val)) {
                $average[$key][] = $val;
            } else if (is_null($val)) {
                // skip it
            } else {
                \Log::error("Got an unexpected value averaging members", [$key, json_encode($val)]);
            }
        } else {
            $average[$key] = $val;
        }
    }

    return $average;
}

function average_array($average, $array) {
    foreach ($array as $key => $val) {
        // echo "Considering $key => " . json_encode($val) . " -> [" . json_encode(array_get($average, $key)) . "]\n" ;
        if (isset($average[$key])) {
            if (is_array($val)) {
                $average[$key] = average_array($average[$key], $val);
            } else if (is_string($val) || is_bool($val)) {
                $average[$key] = $val;
            } else if (is_numeric($val)) {
                $average[$key] = ($val + $average[$key]) / 2;
            } else if (is_null($val)) {
                // skip it
            } else {
                \Log::error("Got an unexpected value averaging members", [$key, json_encode($val)]);
            }
        } else {
            $average[$key] = $val;
        }
    }

    return $average;
}
function round_array($array) {
    foreach ($array as $key => $val) {
        if (is_array($val)) {
            $array[$key] = round_array($val);
        } else if (is_string($val) || is_bool($val)) {
            $array[$key] = $val;
        } else if (is_numeric($val)) {
            $array[$key] = intval(round($val));
        } else if (is_null($val)) {
            // skip it
        } else {
            \Log::error("Got an unexpected value rounding members", [$key, json_encode($val)]);
        }
    }

    return $array;
}