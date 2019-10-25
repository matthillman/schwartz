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
        // 'speed_10', 'speed_15', 'speed_20', 'speed_25',
        // 'offense_100',
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
        return $this->modStats()->where($attribute, '>=', $threshold);
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

    public function getSpeed10Attribute() {
        return $this->modsSpeedGT10()->count();
    }

    public function getSpeed15Attribute() {
        return $this->modsSpeedGT15()->count();
    }

    public function getSpeed20Attribute() {
        return $this->modsSpeedGT20()->count();
    }

    public function getSpeed25Attribute() {
        return $this->modsSpeedGT25()->count();
    }

    public function getOffense100Attribute() {
        return $this->modsOffenseGT100()->count();
    }

    public function getZetasAttribute() {
        return $this->characters->pluck('zetas')->flatten();
    }
}
