<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Guild extends Model
{
    protected $fillable = ['guild_id'];

    protected $appends = ['icon_name', 'raid_tag'];

    public function members() {
        return $this->hasMany(Member::class);
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
