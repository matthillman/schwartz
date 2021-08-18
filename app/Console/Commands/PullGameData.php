<?php

namespace App\Console\Commands;

use DB;
use Cache;
use Storage;
use App\Unit;
use App\Zeta;
use App\Category;
use Carbon\Carbon;
use App\StatModList;
use Illuminate\Console\Command;
use App\Util\JsonObjectConsumer;

use GuzzleHttp\Exception\ClientException;

class PullGameData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swgoh:game-data {--force} {--skip-download}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull the game data json files';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start = Carbon::now();
        $this->info("Starting download…");
        $force = $this->option('force');

        $skipFetch = false;
        if (Storage::disk('game_data')->exists('metadata.json')) {
            $metadata = collect(json_decode(Storage::disk('game_data')->get('metadata.json'), true));
            $newMetadata = collect();
            try {
                $newMetadataResponse = guzzle()->get("https://swgoh.shittybots.me/api/data/metadata.json", [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'shittybot' => config('services.shitty_bot.token'),
                    ],
                ]);
                $newMetadata = collect(json_decode($newMetadataResponse->getBody(), true));
            } catch (ClientException $e) {
                $this->error("  🛑 Error fetching new metadata, bailing: ".$e->getMessage());
                return 1;
            }

            $this->line("Current version: ". $metadata->get('latestGamedataVersion') . ", Server version: " . $newMetadata->get('latestGamedataVersion'));
            $skipFetch = !is_null($metadata->get('latestGamedataVersion')) && $metadata->get('latestGamedataVersion') === $newMetadata->get('latestGamedataVersion');
        }

        if ($force) {
            $this->info('Forcing game data update');
        }

        if ($skipFetch && !$force) {
            $this->info('No new game data, done');
        } else {
            if (!$this->option('skip-download')) {
                $this->info("Fetching data files…");
                $this->getDataFileList()->each(function($file) {
                    try {
                        $this->line("  ➡ Fetching {$file}…");
                        guzzle()->get("https://swgoh.shittybots.me{$file}", [
                            'headers' => [
                                'Accept' => 'application/json',
                                'Content-Type' => 'application/json',
                                'shittybot' => config('services.shitty_bot.token'),
                            ],
                            'sink' => storage_path("app/game_data/".last(explode('/', $file))),
                        ]);
                        sleep(1);
                        $this->line("  ⬅ Fetched.");
                    } catch (ClientException $e) {
                        $this->error("  🛑 Error fetching {$file}: ".$e->getMessage());
                    }
                });
            }

            $this->info("Building language files…");

            // When/if I need multiple language support…
            // collect(Storage::disk('game_data')->files())
            //     ->filter(function($file) {
            //         return preg_match('/[A-Z]{3}_[A-Z]{2}\.json/', $file);
            //     })->each(function($file) {
                    $file = "ENG_US.json";
                    Storage::disk('languages')->put('en/messages.php', "<?php\n\nreturn [");
                    $langString = "";
                    $count = 0;
                    JsonObjectConsumer::parseGameData($file, function($data) use (&$langString, &$count) {
                        $langString .= "\"" . $data['id'] . '" => "' . addcslashes($data['value'], '"') . '",' . "\n";
                        $count += 1;
                        if ($count % 5000 == 0) {
                            Storage::disk('languages')->append('en/messages.php', $langString);
                            $langString = "";
                        }
                    });
                    if (strlen($langString)) {
                        Storage::disk('languages')->append('en/messages.php', $langString);
                        $langString = "";
                    }
                    Storage::disk('languages')->append('en/messages.php', "];\n");
            // });

            $this->info("Parsing unit data…");
            $unitSkills = [];
            JsonObjectConsumer::parseGameData('unitsList.json', function($data) use (&$unitSkills) {
                if (isset($data['baseId'])) {
                    if ($data['rarity'] == 7 && $data['obtainable'] && $data['obtainableTime'] == 0) {
                        $unit = Unit::firstOrNew(['base_id' => $data['baseId']]);
                        $unit->name = $data['nameKey'];
                        $unit->description = $data['descKey'];
                        $unit->image = $data['thumbnailName'];
                        $unit->combat_type = $data['combatType'];
                        $unit->pk = $data['baseId'];
                        $unit->alignment = $data['forceAlignment'];

                        $unit->url = $unit->url ?? '';
                        $unit->power = $unit->power ?? $data['basePower'];
                        $unit->crew_list = $data['crewList'] ?: [];
                        $unit->relic_image = isset($data['relicDefinition']) ? $data['relicDefinition']['nameKey'] : '';
                        $unit->category_list = $data['categoryIdList'];
                        $unit->skills = $data['skillReferenceList'];

                        $unit->abilities = collect([$data['basicAttackRef']])->concat([array_get($data, 'leaderAbilityRef', [])])
                            ->concat($data['limitBreakRefList'])
                            ->concat($data['uniqueAbilityRefList'])
                            ->filter(function($ability) {
                                return isset($ability['abilityId']) && !preg_match('/^hiddenability_/', $ability['abilityId']);
                            });

                        $unit->save();

                        if (!empty($unit->relic_image) && !Storage::disk('images')->exists("gear/$unit->relic_image.png")) {
                            $this->info("Fetching relic image from swgoh.gg");
                            try {
                                guzzle()->get("https://swgoh.gg/static/img/assets/$unit->relic_image.png", [
                                    'sink' => base_path("public/images/gear/$unit->relic_image.png"),
                                ]);
                            } catch (ClientException $e) {
                                $this->error("Image not fetched [{$unit->relic_image}] " . $e->getMessage());
                            }
                        }

                        $downloadedSkillsImages = collect(Storage::disk('images')->files('units/skills'))->map(function($f) {
                            return basename($f);
                        });
                        foreach ($unit->skills->concat($unit->crew_list->pluck('skillReferenceList')->flatten(1))->pluck('skillId') as $skill) {
                            if (!$downloadedSkillsImages->contains("$skill.png")) {
                                $this->info("Fetching ability image from swgoh.gg ($skill)");
                                try {
                                    guzzle()->get("https://swgoh.gg/game-asset/a/$skill.png", [
                                        'sink' => base_path("public/images/units/skills/$skill.png"),
                                    ]);
                                } catch (ClientException $e) {
                                    $this->error("Image not fetched [{$skill}] " . $e->getMessage());
                                }
                            }
                        }

                        $unitSkills[] = ['baseId' => $data['baseId'], 'skills' => collect($data['skillReferenceList'])->pluck('skillId')];
                    }
                    return true;
                }
                return false;
            });
            $unitSkills = collect($unitSkills);

            $this->info("Parsing ability data…");
            $abilityNames = [];
            JsonObjectConsumer::parseGameData('abilityList.json', function($data) use (&$abilityNames) {
                if (isset($data['effectReferenceList'])) {
                    $abilityNames[$data['id']] = $data['nameKey'];

                    if (($data['ultimateChargeRequired'] ?: 0) > 0) {
                        $downloadedSkillsImages = collect(Storage::disk('images')->files('units/skills'))->map(function($f) {
                            return basename($f);
                        });
                        $icon = $data['icon'];
                        if (!$downloadedSkillsImages->contains($data['id'] . ".png")) {
                            $this->info("Fetching ability image from swgoh.gg ($icon)");
                            try {
                                guzzle()->get("https://game-assets.swgoh.gg/$icon.png", [
                                    'sink' => base_path("public/images/units/skills/{$data['id']}.png"),
                                ]);
                            } catch (ClientException $e) {
                                $this->error("Image not fetched [{$icon}] " . $e->getMessage());
                            }
                        }
                    }
                    return true;
                }
                return false;
            });

            $this->info("Parsing skill data…");
            JsonObjectConsumer::parseGameData('skillList.json', function($data) use ($abilityNames, $unitSkills) {
                if (isset($data['tierList'])) {
                    if ($data['isZeta']) {
                        $name = $abilityNames[$data['abilityReference']];
                        $class = (preg_match('/^(.+)skill_/', $data['id'], $matches)) ? trim($matches[1]) : null;

                        $charRefs = $unitSkills->filter(function($value) use ($data) {
                            return $value['skills']->contains($data['id']);
                        });

                        if (is_null($charRefs)) {
                            \Log::warning("Failed to find character for zeta", [$data['id']]);
                            return;
                        }

                        $charRefs->each(function($charRef) use ($data, $name, $class) {
                            $baseId = $charRef['baseId'];

                            $zeta = Zeta::firstOrNew([
                                'skill_id' => $data['id'],
                                'character_id' => $baseId,
                            ]);

                            $zeta->name = $name;
                            $zeta->class = $class;
                            $zeta->skill_id = $data['id'];
                            $zeta->tier = count($data['tierList']) - 1;
                            $zeta->save();
                        });
                    }
                    return true;
                }
                return false;
            });

            $this->info("Parsing mod stat data…");
            $abilityNames = [];
            JsonObjectConsumer::parseGameData('statModList.json', function($data) {
                if (isset($data['slot'])) {
                    $modStat = StatModList::firstOrNew(['id' => $data['id']]);

                    $modStat->slot = +$data['slot'] - 1;
                    $modStat->set = $data['setId'];
                    $modStat->rarity = $data['rarity'];

                    $modStat->save();
                    return true;
                }
                return false;
            });

            $this->info("Parsing category data…");
            JsonObjectConsumer::parseGameData('categoryList.json', function($data) {
                if (isset($data['descKey'])) {
                    $category = Category::firstOrNew(['category_id' => $data['id']]);

                    $category->description = $data['descKey'];
                    $category->visible = $data['visible'];

                    $category->save();
                    return true;
                }
                return false;
            });

            $this->info("Done.");
        }

        Cache::store('game-data')->flush();

        $this->info("Reloading the stat service…");
        $response = guzzle()->post(config('services.swgoh_stats.url') . '/reload', []);
        $this->info($response->getBody());

        $time = Carbon::now()->diffInSeconds($start);
        $this->comment("Returning. Fetching took {$time} seconds.");
        return 0;
    }

    public function getDataFileList() {
        return collect([
            "/api/data/crinolo_core",
            "/api/data/CHS_CN.json",
            "/api/data/CHT_CN.json",
            "/api/data/ENG_US.json",
            "/api/data/FRE_FR.json",
            "/api/data/GER_DE.json",
            "/api/data/IND_ID.json",
            "/api/data/ITA_IT.json",
            "/api/data/JPN_JP.json",
            "/api/data/KOR_KR.json",
            "/api/data/Key_Mapping.json",
            "/api/data/POR_BR.json",
            "/api/data/RUS_RU.json",
            "/api/data/SPA_XM.json",
            "/api/data/THA_TH.json",
            "/api/data/TUR_TR.json",
            "/api/data/abilityList.json",
            "/api/data/arcadeRaidDefinitionList.json",
            "/api/data/arcadeRaidEncounterDefinitionList.json",
            "/api/data/battleEnvironmentsList.json",
            "/api/data/battleTargetingRuleList.json",
            "/api/data/campaignList.json",
            "/api/data/categoryList.json",
            "/api/data/challengeList.json",
            "/api/data/challengeStyleList.json",
            "/api/data/cooldownList.json",
            "/api/data/dailyActionCapList.json",
            "/api/data/effectIconPriorityList.json",
            "/api/data/effectList.json",
            "/api/data/energyRewardList.json",
            "/api/data/environmentCollectionList.json",
            "/api/data/equipmentList.json",
            "/api/data/eventBonusList.json",
            "/api/data/eventSamplingList.json",
            "/api/data/galacticBundleList.json",
            "/api/data/guildBanner.json",
            "/api/data/guildExchangeItemList.json",
            "/api/data/guildRaidList.json",
            "/api/data/helpEntryList.json",
            "/api/data/linkedStoreItemList.json",
            "/api/data/materialList.json",
            "/api/data/modRecommendationList.json",
            "/api/data/mysteryBoxList.json",
            "/api/data/mysteryStatModList.json",
            "/api/data/participation.json",
            "/api/data/persistentVfxList.json",
            "/api/data/playerPortraitList.json",
            "/api/data/playerTitleList.json",
            "/api/data/powerUpBundleList.json",
            "/api/data/raidConfigList.json",
            "/api/data/raidStatus.json",
            "/api/data/recipeList.json",
            "/api/data/relicTierDefinitionList.json",
            "/api/data/requirementList.json",
            "/api/data/scavengerConversionSetList.json",
            "/api/data/seasonDefinitionList.json",
            "/api/data/seasonDivisionDefinitionList.json",
            "/api/data/seasonLeagueDefinitionList.json",
            "/api/data/seasonRewardTableList.json",
            "/api/data/skillList.json",
            "/api/data/socialStatus.json",
            "/api/data/starterGuildList.json",
            "/api/data/statModList.json",
            "/api/data/statModSetList.json",
            "/api/data/statProgressionList.json",
            "/api/data/systemModifierList.json",
            "/api/data/tableList.json",
            "/api/data/targetingSetList.json",
            "/api/data/territoryBattleDefinitionList.json",
            "/api/data/territoryTournamentDefinitionList.json",
            "/api/data/territoryTournamentMatchmakingDescKey.json",
            "/api/data/territoryWarDefinitionList.json",
            "/api/data/timeZoneChangeConfig.json",
            "/api/data/unitGuideDefinitionList.json",
            "/api/data/unitsList.json",
            "/api/data/unlockAnnouncementDefinitionList.json",
            "/api/data/warDefinitionList.json",
            "/api/data/xpTableList.json",
            "/api/data/metadata.json",
        ]);
    }
}
