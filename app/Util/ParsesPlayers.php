<?php

namespace App\Util;

use DB;
use Storage;
use App\Mod;
use App\Zeta;
use App\Unit;
use App\Member;
use App\ModUser;
use App\Character;
use App\MembersRaw;
use App\StatModList;
use App\CharacterZeta;

use SwgohHelp\Enums\ModSet;
use SwgohHelp\Enums\ModSlot;
use SwgohHelp\Enums\UnitStat;
use SwgohHelp\Enums\PlayerStatsIndex;

trait ParsesPlayers {

    private $logPrefix = '';

    private function getZetaList() {
        static $zetaList;
        if (is_null($zetaList)) {
            $zetaList = Zeta::all();
        }
        return $zetaList;
    }

    private function getGPTables() {
        static $gpTables;
        if (is_null($gpTables)) {
            $gpTables = json_decode(Storage::disk('local')->get('gameData.json'), true)['gpTables'];
        }
        return $gpTables;
    }

    function getAdjustedPower($unit, $characterLookup = []) {
        if ($unit['combat_type'] == 1) {
            return $this->getRelicAdjustedUnitGP($unit);
        } else if ($unit['combat_type'] == 2) {
            return $this->getShipGP($unit, $characterLookup);
        }

        return $unit['power'];
    }

    function getRelicAdjustedUnitGP($unit) {
        if ($unit['combat_type'] != 1 || $unit['relic'] < 2) { return $unit['power']; }

        static $relicBonus = [ 0, 759, 1594, 2505, 3492, 4554, 6072, 7969 ];
        return $unit['power'] + $relicBonus[$unit['relic'] - 2];
    }

    function getShipGP($unit, $characterLookup) {
        if ($unit['combat_type'] != 2) { return $unit['power']; }

        $rawData = $unit['raw'];
        $characterLookup = collect($characterLookup)->keyBy('unit_name');
        $gpTable = $this->getGPTables();

        $gpRarity = array_get($gpTable['crewRarityGP'], $rawData['rarity'], 0);
        $gpCrewSize = array_get($gpTable['crewSizeFactor'], count($rawData['crew']), 0);
        $gpLevel = array_get($gpTable['shipLevelGP'], $rawData['level'], 0);
        list($gpAbility, $gpHardware) = collect($rawData['skills'])
            ->reduce(function($total, $skill) use ($gpTable) {
                if ($skill['tiers'] === 3 /* && combatType == 2*/) { // commenting here for future reference
                    $total[1] += array_get($gpTable['abilitySpecialCR']['hardware'], $skill['tier'], 0);
                } else {
                    $total[0] += array_get($gpTable['shipAbilityLevelGP'], $skill['tier'], 0);
                }

                return $total;

            }, [0, 0]);
        $gpModifier = array_get($gpTable['shipRarityFactor'], $rawData['rarity'], 0);

        $gpShipPower = 0;
        $gpCrewPower = 0;
        $gpCrew = 0;
        $gpTotal = 0;

        list($gpCrewPower, $gpCrew) = collect($rawData['crew'])
            ->reduce(function($sums, $crewMember) use ($characterLookup, $gpModifier, $gpCrewSize) {
                $crew = $characterLookup->get($crewMember['unitId']);
                $cp = $crew['power'] * $gpModifier * $gpCrewSize;
                return [$sums[0] + $cp, $sums[1] + $crew['power']];
            }, [0, 0]);


        if (count($rawData['crew']) == 0) {
            // $gpPerAbilityModifier = $gpTable['crewlessAbilityFactor'][count($rawData['skills'] ?? 3)];
            $gpCrew = ($gpLevel * 3.5 + $gpAbility * 5.74 + $gpHardware * 1.61) * $gpModifier;
            $gpTotal = ($gpCrew + $gpLevel + $gpAbility + $gpHardware) * 1.5;
        } else {
            $gpCrew = $gpCrew * $gpModifier * $gpCrewSize;
            $gpShipPower = ($gpCrew / 2) + (($gpLevel + $gpAbility + $gpHardware) * 1.5);
            $gpTotal = $gpShipPower + $gpCrewPower;
        }

        $gpTotal = intval($gpTotal);

        return $gpTotal;
    }

    public function translate($member_data) {
        if (!config('services.shitty_bot.active')) {
            $this->info($this->logPrefix . 'Skipping Translation');
            return $member_data;
        }
        $this->info($this->logPrefix . 'Doing Translation');
        $translated = collect($member_data)->only(
            'name',
            'level',
            'allyCode',
            'playerId',
            'guildName',
            'guildBannerColor',
            'guildBannerLogo'
        )->toArray();

        $translated['titles'] = [
            'selected' => array_get($member_data, 'selectedPlayerTitle.id'),
            'unlocked' => collect($member_data['unlockedPlayerTitleList'])->pluck('id'),
        ];

        $translated['portraits'] = [
            'selected' => array_get($member_data, 'selectedPlayerPortrait.id'),
            'unlocked' => collect($member_data['unlockedPlayerPortraitList'])->pluck('id'),
        ];

        $translated['stats'] = $member_data['profileStatList'];
        $translated['arena'] = $member_data['pvpProfileList'];

        $translated['roster'] = collect($member_data['rosterUnitList'])->map(function($unit) {
            list($baseId, ) = explode(':', $unit['definitionId']);
            $unitData = Unit::where(['base_id' => $baseId])->firstOrFail();

            $r = [
                'defId' => $unitData->base_id,
                'nameKey' => $unitData->name,
                'rarity' => $unit['currentRarity'],
                'level' => $unit['currentLevel'],
                'gear' => $unit['currentTier'],
                'combatType' => $unitData->combat_type,
                'crew' => $unitData->crew_list,
                'gp' => 0,
                'relic' => array_get($unit, 'relic', []),
                'equipped' => array_get($unit, 'equipmentList', []),
                'skills' => collect(array_get($unit, 'skillList', []))->map(function($skill) {
                    $skill['tier'] += 2;
                    return $skill;
                }),
                'mods' => collect($unit['equippedStatModList'])->map(function($modData) {
                    $modStat = StatModList::findOrFail($modData['definitionId']);
                    return [
                        'id' => $modData['id'],
                        'definitionId' => $modData['definitionId'],
                        'level' => $modData['level'],
                        'tier' => $modData['tier'],
                        'slot' => $modStat->slot,
                        'set' => $modStat->set,
                        'pips' => $modStat->rarity,
                        'primaryStat' => [
                            'unitStat' => $modData['primaryStat']['stat']['unitStatId'],
                            'value' =>  $modData['primaryStat']['stat']['unscaledDecimalValue'] / $this->statMultiplierFor($modData['primaryStat']['stat']['unitStatId']),
                        ],
                        'secondaryStat' => collect($modData['secondaryStatList'])->map(function($secondary) {
                            return [
                                'unitStat' => $secondary['stat']['unitStatId'],
                                'value' => $secondary['stat']['unscaledDecimalValue'] / $this->statMultiplierFor($secondary['stat']['unitStatId']),
                                'roll' => $secondary['statRolls'],
                            ];
                        }),
                    ];
                }),
            ];
            return $r;
        });

        return $translated;
    }

    private function statMultiplierFor($stat) {
        switch ($stat) {
            case 1:  // Health
            case 5:  // Speed
            case 28: // Protection
            case 41: // Offense
            case 42: // Defense
              // Flat stats
              return 100000000;
            default:
              // Percent stats
              return 1000000;
          }
    }

    public function parseMember($member_data, $guild, $logPrefix = '   ') {
        $this->logPrefix = $logPrefix;
        $zetaList = $this->getZetaList();
        $member = Member::firstOrNew(['ally_code' => (string)$member_data['allyCode']]);

        $raw_member_data = $member_data;

        $member_data = $this->translate($member_data);

        $member_data = stats()->addStatsTo([$member_data])->first();

        $ally = $member_data['allyCode'];
        $member->url = "/p/{$ally}/characters/";
        $member->player = $member_data['name'];
        $member->player_id = array_get($member_data, 'playerId', '');

        $member->arena = $member_data['arena'];
        $member->level = $member_data['level'];
        $member->title = array_get($member_data, 'titles.selected', 'Patron');
        $member->portrait = array_get($member_data, 'portraits.selected', 'NONE');
        if (is_null($member->title)) {
            $member->title = 'Patron';
        }
        if (is_null($member->portrait)) {
            $member->portrait = 'NONE';
        }

        // $stats = collect($member_data['stats']);
        // Fucking game
        // $member->gp = $stats->where('index', PlayerStatsIndex::gp)->pluck('value')->first();
        // $member->character_gp = $stats->where('index', PlayerStatsIndex::charGP)->pluck('value')->first();
        // $member->ship_gp = $stats->where('index', PlayerStatsIndex::shipGP)->pluck('value')->first();

        if (!is_null($guild)) {
            $member->guild()->associate($guild);
        }
        $member->save();
        $mr = MembersRaw::firstOrNew(['member_id' => $member->id]);
        $mr->data = $raw_member_data;
        $mr->save();

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
            $isChar = $unit['combatType'] == 1;
            if ($isChar) {
                $this->line("Relic level" . array_get($unit, 'relic.currentTier', 0));
            }
            $character = [
                'member_id' => $member->id,
                'unit_name' => $unit['defId'],
                'gear_level' => $unit['gear'],
                'power' => $unit['gp'],
                'level' => $unit['level'],
                'combat_type' => $unit['combatType'],
                'rarity' => $unit['rarity'],
                'relic' => array_get($unit, 'relic.currentTier', 0),
                'stats' => $unit['stats'],
                'raw' => collect($unit)->except('stats')->toArray(),
            ];
            // if ($isChar) {
            //     $character['power'] = $this->getAdjustedPower($character);
            // }
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

        // $characterUnits = $chars->where('combat_type', 1);
        // $chars->transform(function($ship) use ($characterUnits) {
        //     if ($ship['combat_type'] == 2) {
        //         $ship['power'] = $this->getAdjustedPower($ship, $characterUnits);
        //     }
        //     return $ship;
        // });

        // Fucking game
        $member->character_gp = $chars->where('combat_type', 1)->pluck('power')->sum();
        $member->ship_gp = $chars->where('combat_type', 2)->pluck('power')->sum();
        $member->gp = $member->character_gp + $member->ship_gp;
        $member->save();

        $this->info("${logPrefix}Updated player GP to {$member->gp} ({$member->character_gp} C, {$member->ship_gp} S)");

        $cCount = $chars->count();
        $this->info("${logPrefix}➡ Doing the character insert (${cCount} rows)");
        Character::upsert($chars->toArray(), "(member_id, unit_name)");

        $this->info("${logPrefix}➡ Trimming raw data");
        DB::delete('delete from characters_raw where character_id in (select id from characters where member_id = ?);', [$member->id]);
        $this->info("${logPrefix}➡ Inserting new raw data");
        DB::insert('insert into characters_raw (character_id, data) select id, raw from characters where member_id = ?', [$member->id]);
        $this->info("${logPrefix}➡ Trimming char table");
        DB::update('update characters set raw = ? where member_id = ?', ['[]', $member->id]);
        $this->info("${logPrefix}⬅ Done with character insert.");

        $skills = $roster->pluck('skills')->flatten(1)->where('tier', 8)->pluck('id');
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