<?php

namespace App\Util;

use App\Mod;
use App\Zeta;
use App\Member;
use App\ModUser;
use App\Character;
use App\CharacterZeta;

use SwgohHelp\Enums\ModSet;
use SwgohHelp\Enums\ModSlot;
use SwgohHelp\Enums\UnitStat;
use SwgohHelp\Enums\PlayerStatsIndex;

trait ParsesPlayers {

    private function getZetaList() {
        static $zetaList;
        if (is_null($zetaList)) {
            $zetaList = Zeta::all();
        }
        return $zetaList;
    }

    public function parseMember($member_data, $guild, $logPrefix = '   ') {
        $zetaList = $this->getZetaList();
        $member = Member::firstOrNew(['ally_code' => (string)$member_data['allyCode']]);

        $member_data = stats()->addStatsTo([$member_data])->first();

        $ally = $member_data['allyCode'];
        $member->url = "/p/{$ally}/characters/";
        $member->player = $member_data['name'];

        $stats = collect($member_data['stats']);

        $member->gp = $stats->where('index', PlayerStatsIndex::gp)->pluck('value')->first();
        $member->character_gp = $stats->where('index', PlayerStatsIndex::charGP)->pluck('value')->first();
        $member->ship_gp = $stats->where('index', PlayerStatsIndex::shipGP)->pluck('value')->first();

        if (!is_null($guild)) {
            $member->guild()->associate($guild);
        }
        $member->save();

        $modUser = ModUser::firstOrNew(['name' => (string)$ally]);
        $modUser->last_scrape = new \DateTime;
        $modUser->save();

        $updated[] = $member->id;

        $this->comment("${logPrefix}Looping over units for {$member->player}…");
        $roster = collect($member_data['roster']);
        $mappedRoster = $roster->map(function($unit) use ($member, $modUser) {
            if (is_null($unit['combatType'])) {
                $unit['combatType'] = 1;
            }
            $isChar = $unit['combatType'] === 1;
            $character = [
                'member_id' => $member->id,
                'unit_name' => $unit['defId'],
                'gear_level' => $unit['gear'],
                'power' => $unit['gp'],
                'level' => $unit['level'],
                'combat_type' => $unit['combatType'],
                'rarity' => $unit['rarity'],
                'relic' => $isChar ? $unit['relic']['currentTier'] : 0,
                'stats' => $unit['stats'],
                'raw' => collect($unit)->except('stats')->toArray(),
            ];
            $mods = collect($unit['mods'] ?? [])->map(function($mod) use ($character, $modUser) {
                $modItem = [
                    "uid" => $mod["id"],
                    'slot' => (new ModSlot(+$mod['slot']))->getKey(),
                    'set' => (new ModSet(+$mod['set']))->getKey(),
                    "pips" => $mod["pips"],
                    "level" => $mod["level"],
                    "name" => "",
                    "location" => $character['unit_name'],
                    "mod_user_id" => $modUser->id,
                    "tier" => $mod["tier"],
                    "primary_type" => (new UnitStat($mod["primaryStat"]["unitStat"]))->getKey(),
                    "primary_value" => $mod["primaryStat"]["value"],
                    'raw' => $mod,
                ];

                collect([1, 2, 3, 4])->each(function($index) use ($mod, &$modItem) {
                    $statIndex = $index - 1;
                    $secondaryType = array_get($mod, "secondaryStat.${statIndex}.unitStat", null);
                    $secondaryType = $secondaryType !== null ? (new UnitStat($secondaryType))->getKey() : null;
                    $modItem["secondary_${index}_type"] = $secondaryType;
                    $modItem["secondary_${index}_value"] = array_get($mod, "secondaryStat.${statIndex}.value", null);
                });

                return $modItem;
            });

            return ['char' => $character, 'mods' => $mods];
        });

        $chars = $mappedRoster->pluck('char');
        $mods = $mappedRoster->pluck('mods')->flatten(1);

        $cCount = $chars->count();
        $this->info("${logPrefix}➡ Doing the character insert (${cCount} rows)");
        Character::upsert($chars->toArray(), "(member_id, unit_name)");
        $this->info("${logPrefix}⬅ Done with character insert.");

        $skills = $roster->pluck('skills')->flatten(1)->where('isZeta', true)->where('tier', 8)->pluck('id');
        $memberChars = $member->characters()->get();
        $zetas = $zetaList->whereIn('skill_id', $skills)->map(function($zeta) use ($memberChars) {
            $character = $memberChars->where('unit_name', $zeta->character_id)->first();
            return [
                'zeta_id' => $zeta->id,
                'character_id' => $character->id,
            ];
        });

        $zCount = $zetas->count();
        if ($zCount > 0) {
            $this->info("${logPrefix}➡ Doing the zeta insert (${zCount} rows)");
            CharacterZeta::upsert($zetas->toArray(), "(character_id, zeta_id)");
            $this->info("${logPrefix}⬅ Done with zeta insert.");

            $existing = $member->characters->map(function($c) { return $c->zetas; })->collapse()->pluck('id');
            $diff = $existing->diff($zetas->pluck('zeta_id'))->values();
            $zCount = $diff->count();
            if ($zCount > 0) {
                $this->info("${logPrefix}➡ Deleting extra zetas (${zCount} rows)");
                CharacterZeta::whereIn('zeta_id', $diff)->delete();
                $this->info("${logPrefix}⬅ Extra zetas deleted.");
            }
        } else {
            $this->info("${logPrefix}No zetas to insert.");
        }

        $mCount = $mods->count();
        $this->info("${logPrefix}➡ Doing the mod insert for {$modUser->name} (${mCount} rows)");
        Mod::upsert($mods->toArray(), "(uid)");
        $this->info("${logPrefix}⬅ Done with mod insert.");

        $this->comment("${logPrefix}{$member->player} done.");

        return $member->id;
    }
}