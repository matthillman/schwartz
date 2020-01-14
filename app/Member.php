<?php

namespace App;

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
        'gear_12', 'gear_13',
        'relic_5', 'relic_6', 'relic_7',
        // 'speed_10', 'speed_15', 'speed_20', 'speed_25',
        // 'offense_100',
    ];

    protected $casts = [
        'arena' => 'array',
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

    private function modsAtOrOver($threshold, $attribute = 'speed') {
        static $modCounts;
        if (is_null($modCounts)) {
            $modCounts = \DB::table('mod_stats')
                ->join('mod_users', 'mod_stats.mod_user_id', '=', 'mod_users.id')
                ->selectRaw("
                    mod_users.name,
                    sum(case when mod_stats.pips >= 6 then 1 else 0 end) as dot_6,
                    sum(case when mod_stats.speed >= 25 then 1 else 0 end) as speed_25,
                    sum(case when mod_stats.speed >= 20 then 1 else 0 end) as speed_20,
                    sum(case when mod_stats.speed >= 15 then 1 else 0 end) as speed_15,
                    sum(case when mod_stats.speed >= 10 then 1 else 0 end) as speed_10,
                    sum(case when mod_stats.offense >= 100 then 1 else 0 end) as offense_100
                ")
                ->groupBy('mod_users.name')
                ->where('mod_users.name', $this->ally_code)
                ->first();
        }

        return $modCounts->{"{$attribute}_{$threshold}"};
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
