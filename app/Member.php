<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['url', 'ally_code'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'guild_name',
        'profile_url',
        'gear_12', 'gear_13',
        'relic_5', 'relic_6', 'relic_7',
        // 'speed_10', 'speed_15', 'speed_20', 'speed_25',
        // 'offense_100',
    ];

    protected $casts = [
        'arena' => 'array',
    ];
    protected $hidden = [
        'raw',
    ];

    public function characters() {
        return $this->hasMany(Character::class);
    }

    public function mods() {
        return $this->hasManyThrough(Mod::class, ModUser::class, 'name', 'mod_user_id', 'ally_code', 'id');
    }

    public function characterSet(array $characters) { // List of base_ids
        return collect([
            'url' => $this->url,
            'ally_code' => $this->ally_code,
            'player' => $this->player,
            'characters' => $this->characters->whereIn('unit_name', $characters)->values()
        ]);
    }

    private function modStats() {
        return $this->hasManyThrough(ModStat::class, ModUser::class, 'name', 'mod_user_id', 'ally_code', 'id');
    }

    public function raw() {
        return $this->hasOne(MembersRaw::class);
    }

    private function modsAtOrOver($threshold, $attribute = 'speed') {
        static $modCounts;
        if (is_null($modCounts)) {
            // $modCounts = DB::table('mod_stats')
            //     ->join('mod_users', 'mod_stats.mod_user_id', '=', 'mod_users.id')
            //     ->selectRaw("
            //         mod_users.name,
            //         sum(case when mod_stats.pips >= 6 then 1 else 0 end) as dot_6,
            //         sum(case when mod_stats.speed >= 25 then 1 else 0 end) as speed_25,
            //         sum(case when mod_stats.speed >= 20 then 1 else 0 end) as speed_20,
            //         sum(case when mod_stats.speed >= 15 then 1 else 0 end) as speed_15,
            //         sum(case when mod_stats.speed >= 10 then 1 else 0 end) as speed_10,
            //         sum(case when mod_stats.offense >= 100 then 1 else 0 end) as offense_100
            //     ")
            //     ->groupBy('mod_users.name')
            //     ->where('mod_users.name', $this->ally_code)
            //     ->first();

            $modCounts = head(DB::select("
                select
                    name,
                    sum((pips >= 6)::int) as dot_6,
                    sum((speed >= 25)::int) as speed_25,
                    sum((speed >= 20)::int) as speed_20,
                    sum((speed >= 15)::int) as speed_15,
                    sum((speed >= 10)::int) as speed_10,
                    sum((offense >= 100)::int) as offense_100
                from (
                    select
                        mod_users.name,
                        mods.pips,
                        CASE
                            WHEN secondary_1_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_1_value)::numeric
                            WHEN secondary_2_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_2_value)::numeric
                            WHEN secondary_3_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_3_value)::numeric
                            WHEN secondary_4_type = 'UNITSTATSPEED' THEN trim(trailing '%' from secondary_4_value)::numeric
                            ELSE 0 END as speed,
                        CASE
                            WHEN secondary_1_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_1_value)::numeric
                            WHEN secondary_2_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_2_value)::numeric
                            WHEN secondary_3_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_3_value)::numeric
                            WHEN secondary_4_type = 'UNITSTATOFFENSE' THEN trim(trailing '%' from secondary_4_value)::numeric
                            ELSE 0 END as offense
                    from mods
                    inner join mod_users on mods.mod_user_id = mod_users.id
                    where mod_users.name = :ally
                ) mod_totals
                group by name
                ", ['ally' => $this->ally_code]));
        }

        return $modCounts->{"{$attribute}_{$threshold}"};
    }

    public function getProfileUrlAttribute() {
        return route('member.profile', ['allyCode' => $this->ally_code]);
    }

    public function mods6dot() {
        return $this->modsAtOrOver(6, 'dot');
    }

    public function modsSpeedGT10() {
        return $this->modsAtOrOver(10);
    }

    public function modsSpeedGT15() {
        return $this->modsAtOrOver(15);
    }

    public function modsSpeedGT20() {
        return $this->modsAtOrOver(20);
    }

    public function modsSpeedGT25() {
        return $this->modsAtOrOver(25);
    }

    public function modsOffenseGT100() {
        return $this->modsAtOrOver(100, 'offense');
    }

    public function gear12() {
        return $this->characters()->where('gear_level', '=', 12);
    }

    public function gear13() {
        return $this->characters()->where('gear_level', '=', 13);
    }

    public function relic3() {
        return $this->characters()->where('relic', '=', 3 + 2);
    }
    public function relic5() {
        return $this->characters()->where('relic', '=', 5 + 2);
    }
    public function relic6() {
        return $this->characters()->where('relic', '=', 6 + 2);
    }
    public function relic7() {
        return $this->characters()->where('relic', '=', 7 + 2);
    }

    public function guild() {
        return $this->belongsTo(Guild::class)->withDefault();
    }

    public function getGuildNameAttribute() {
        return $this->guild->name;
    }

    public function getGear12Attribute() {
        return $this->gear12()->count();
    }

    public function getGear13Attribute() {
        return $this->gear13()->count();
    }

    public function getRelic3Attribute() {
        return $this->relic3()->count();
    }

    public function getRelic5Attribute() {
        return $this->relic5()->count();
    }

    public function getRelic6Attribute() {
        return $this->relic6()->count();
    }

    public function getRelic7Attribute() {
        return $this->relic7()->count();
    }

    public function getSixDotAttribute() {
        return $this->mods6dot();
    }

    public function getSpeed10Attribute() {
        return $this->modsSpeedGT10();
    }

    public function getSpeed15Attribute() {
        return $this->modsSpeedGT15();
    }

    public function getSpeed20Attribute() {
        return $this->modsSpeedGT20();
    }

    public function getSpeed25Attribute() {
        return $this->modsSpeedGT25();
    }

    public function getOffense100Attribute() {
        return $this->modsOffenseGT100();
    }

    public function getZetasAttribute() {
        return $this->characters->pluck('zetas')->flatten();
    }
}
