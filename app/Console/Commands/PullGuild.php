<?php

namespace App\Console\Commands;

use DB;
use App\Zeta;
use App\Guild;
use App\Member;
use App\Character;
use Carbon\Carbon;
use App\CharacterZeta;
use App\Parsers\GuildParser;
use Illuminate\Console\Command;

class PullGuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pull:guild {guild}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull all characters for a guild from swogh.gg';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start = Carbon::now();
        $guild = Guild::firstOrNew(['guild_id' => $this->argument('guild')]);
        $name = $guild->name ?? 'GUILD ' . $guild->guild_id;
        $this->info("Starting GuildParser for {$name}…");
        $parser = (new GuildParser($this->argument('guild')))->scrape();
        $guild->url = $parser->url();
        $guild->name = $parser->name();
        $guild->gp = $parser->gp();
        $guild->save();
        $this->info("Guild saved.");

        $this->info("Starting API pull…");
        $response = guzzle()->get("https://swgoh.gg/api/guilds/{$guild->guild_id}/units/");
        $json_string = (string)$response->getBody();

        $units = collect(json_decode($json_string, true));
        $this->info("API pull finished.");

        $this->info("Dissociating all guild members…");
        DB::transaction(function() use ($guild) {
            $guild->members()->each(function($member) {
                $member->guild()->dissociate();
                $member->save();
            });
        });
        $this->info("Dissociation done.");

        $guildMemberCache = [];
        $zetaList = Zeta::all();
        $this->info("Starting API results loop…");

        $charactersToInsert = $units->flatMap(function($data, $unit) use ($guild, $parser, &$guildMemberCache, $zetaList) {
            $this->comment("   Looping over members for {$unit}…");
            $chars = collect($data)->map(function($member_data) use ($guild, $unit, $parser, &$guildMemberCache, $zetaList) {
                // DB::transaction(function() use ($guild, $member_data, $unit, $parser, &$guildMemberCache, $zetaList) {
                    if (!isset($member_data['url'])) { return; }
                    if (isset($guildMemberCache[$member_data['url']])) {
                        $member = $guildMemberCache[$member_data['url']];
                    } else {
                        $member = Member::firstOrNew(['url' => $member_data['url']]);

                        $member->url = $member_data['url'];
                        $member->player = $member_data['player'];

                        $gp = isset($parser->memberGP()[$member->url]) ? $parser->memberGP()[$member->url] : ['gp' => 0, 'character_gp' => 0, 'ship_gp' => 0];
                        $member->gp = $gp['gp'];
                        $member->character_gp = $gp['character_gp'];
                        $member->ship_gp = $gp['ship_gp'];

                        $member->guild()->associate($guild);
                        $member->save();

                        $guildMemberCache[$member_data['url']] = $member;
                    }

                    $character = [
                        'member_id' => $member->id,
                        'unit_name' => $unit,
                        'gear_level' => $member_data['gear_level'],
                        'power' => $member_data['power'],
                        'level' => $member_data['level'],
                        'combat_type' => $member_data['combat_type'],
                        'rarity' => $member_data['rarity'],
                    ];

                    // if (isset($parser->zetas()[$member->url])) {
                    //     $memberZetas = $parser->zetas()[$member->url];
                    //     if (isset($memberZetas[$character->unit_name])) {
                    //         $zetas = $memberZetas[$character->unit_name];

                    //         $ids = $zetaList->where('character_id', $character->unit_name)
                    //             ->whereIn('name', $zetas)
                    //             ->pluck('id')
                    //             ->all();

                    //         $character->zetas()->sync($ids);
                    //     }
                    // }
                // });
                return $character;
            });
            $this->info("   $unit done.");
            return $chars;
        })->reject(function ($value) { return is_null($value); });
        $this->info("API results parsed.");

        $cCount = $charactersToInsert->count();
        $this->info("Doing the character insert (${cCount} rows)");
        Character::upsert($charactersToInsert->toArray(), "(member_id, unit_name)");
        $this->info("Done with character insert.");

        $zetasToInsert = collect($parser->zetas())->flatMap(function($zetaInfo, $memberURL) use ($guildMemberCache, $zetaList) {
            if (!isset($guildMemberCache[$memberURL])) { return null; }
            $memberChars = $guildMemberCache[$memberURL]->characters;
            return collect($zetaInfo)->flatMap(function($zetas, $unit) use ($memberChars, $zetaList) {
                $character = $memberChars->where('unit_name', $unit)->first();
                return $zetaList->where('character_id', $character->unit_name)
                    ->whereIn('name', $zetas)
                    ->map(function($zeta) use ($character) {
                        return [
                            'zeta_id' => $zeta->id,
                            'character_id' => $character->id,
                        ];
                    });
            });
        })->reject(function ($value) { return is_null($value); });

        $zCount = $zetasToInsert->count();
        $this->info("Doing the zeta insert (${zCount} rows)");
        CharacterZeta::upsert($zetasToInsert->toArray(), "(character_id, zeta_id)");
        $this->info("Done with zeta insert.");

        $time = Carbon::now()->diffInSeconds($start);
        $this->info("Returning. Scrape took {$time} seconds.");
        return 0;
    }
}
