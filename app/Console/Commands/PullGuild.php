<?php

namespace App\Console\Commands;

use DB;
use App\Mod;
use App\Zeta;
use App\Guild;
use App\Member;
use App\ModUser;
use App\Character;
use Carbon\Carbon;
use App\CharacterZeta;
use App\Parsers\SH\GuildParser;
use Illuminate\Console\Command;
use App\Parsers\SH\Enums\PlayerStats;

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

        $parser = new GuildParser($this->argument('guild'));
        $this->info("Starting API pull…");

        $parser->scrape();
        $this->info("API pull finished.");

        $this->info("Saving basic info.");
        $guild->url = $parser->url();
        $guild->name = $parser->name();
        $guild->gp = $parser->gp();
        $guild->save();
        $this->info("Guild saved.");

        $this->info("Dissociating all guild members…");
        DB::transaction(function() use ($guild) {
            $guild->members()->each(function($member) {
                $member->guild()->dissociate();
                $member->save();
            });
        });
        $this->info("Dissociation done.");

        $guildMembers = [];
        $zetaList = Zeta::all();
        $this->info("Starting API results loop…");

        $charactersToInsert = $parser->members()->flatMap(function($member_data) use ($guild, &$guildMembers) {
            $member = Member::firstOrNew(['ally_code' => (string)$member_data['allyCode']]);

            $ally = $member_data['allyCode'];
            $member->url = "/p/{$ally}/collection/";
            $member->player = $member_data['name'];

            $stats = collect($member_data['stats']);
            $member->gp = $stats->where('nameKey', PlayerStats::gp)->pluck('value')->first();
            $member->character_gp = $stats->where('nameKey', PlayerStats::charGP)->pluck('value')->first();
            $member->ship_gp = $stats->where('nameKey', PlayerStats::shipGP)->pluck('value')->first();

            $member->guild()->associate($guild);
            $member->save();

            $guildMembers[$member->ally_code] = $member;

            $this->comment("   Looping over units for {$member->player}…");
            $chars = collect($member_data['roster'])->map(function($unit) use ($member) {
                $character = [
                    'member_id' => $member->id,
                    'unit_name' => $unit['defId'],
                    'gear_level' => $unit['gear'],
                    'power' => $unit['gp'],
                    'level' => $unit['level'],
                    'combat_type' => 1,
                    'rarity' => $unit['rarity'],
                ];
                return $character;
            });
            $this->info("   {$member->player} done.");
            return $chars;
        })->reject(function ($value) { return is_null($value); });

        $this->info("API results parsed.");

        $cCount = $charactersToInsert->count();
        $this->info("Doing the character insert (${cCount} rows)");
        Character::upsert($charactersToInsert->toArray(), "(member_id, unit_name)");
        $this->info("Done with character insert.");

        $rosterByAllyCode = $parser->members()->pluck('roster', 'allyCode');

        $zetasToInsert = $rosterByAllyCode->flatMap(function($roster, $allyCode) use ($guildMembers, $zetaList) {
            if (!isset($guildMembers[$allyCode])) { return null; }
            $memberChars = $guildMembers[$allyCode]->characters;
            $skills = collect($roster)->pluck('skills')->flatten(1)->where('isZeta', true)->where('tier', 8)->pluck('id');
            return $zetaList->whereIn('skill_id', $skills)->map(function($zeta) use ($memberChars) {
                $character = $memberChars->where('unit_name', $zeta->character_id)->first();
                return [
                    'zeta_id' => $zeta->id,
                    'character_id' => $character->id,
                ];
            });
        })->reject(function ($value) { return is_null($value); });

        $zCount = $zetasToInsert->count();
        $this->info("Doing the zeta insert (${zCount} rows)");
        CharacterZeta::upsert($zetasToInsert->toArray(), "(character_id, zeta_id)");
        $this->info("Done with zeta insert.");

        // $rosterByAllyCode->each(function($roster, $allyCode) {
        //     $modUser = ModUser::firstOrNew(['name' => (string)$allyCode]);
        //     $modUser->last_scrape = new \DateTime;
        //     $modUser->save();

        //     $mods = collect($roster)->pluck('mods', 'defId');

        //     $modUser->mods()->whereNotIn('uid', $mods->flatten(1)->pluck('id'))->delete();

        //     $modsToInsert = $mods->flatMap(function($charMods, $charID) use ($modUser) {
        //         return collect($charMods)->map(function($mod) use ($charID, $modUser) {
        //             return [
        //                 "uid" => $mod["id"],
        //                 "slot" => $mod["slot"],
        //                 "set" => $mod["set"],
        //                 "pips" => $mod["pips"],
        //                 "level" => $mod["level"],
        //                 "name" => "",
        //                 "location" => $charID,
        //                 "mod_user_id" => $modUser->id,
        //                 "tier" => $mod["tier"],
        //                 "primary_type" => $mod["primaryBonusType"],
        //                 "primary_value" => $mod["primaryBonusValue"],
        //                 "secondary_1_type" => $mod["secondaryType_1"],
        //                 "secondary_1_value" => $mod["secondaryValue_1"],
        //                 "secondary_2_type" => $mod["secondaryType_2"],
        //                 "secondary_2_value" => $mod["secondaryValue_2"],
        //                 "secondary_3_type" => $mod["secondaryType_3"],
        //                 "secondary_3_value" => $mod["secondaryValue_3"],
        //                 "secondary_4_type" => $mod["secondaryType_4"],
        //                 "secondary_4_value" => $mod["secondaryValue_4"],
        //               ];
        //         });
        //     });

        //     $mCount = $modsToInsert->count();
        //     $this->info("Doing the mod insert for {$modUser->name} (${mCount} rows)");
        //     Mod::upsert($modsToInsert->toArray(), "(uid)");
        //     $this->info("Done with mod insert.");
        // });

        $time = Carbon::now()->diffInSeconds($start);
        $this->info("Returning. Scrape took {$time} seconds.");
        return 0;
    }
}
