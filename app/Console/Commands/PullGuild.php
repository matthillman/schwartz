<?php

namespace App\Console\Commands;

use DB;
use App\Zeta;
use App\Guild;
use App\Member;
use App\Character;
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
        $guild = Guild::firstOrNew(['guild_id' => $this->argument('guild')]);
        $parser = (new GuildParser($this->argument('guild')))->scrape();
        $guild->url = $parser->url();
        $guild->name = $parser->name();
        $guild->gp = $parser->gp();
        $guild->save();

        $response = guzzle()->get("https://swgoh.gg/api/guilds/{$guild->guild_id}/units/");
        $json_string = (string)$response->getBody();

        $units = collect(json_decode($json_string, true));

        DB::transaction(function() use ($guild) {
            $guild->members()->each(function($member) {
                $member->guild()->dissociate();
                $member->save();
            });
        });

        $units->each(function($data, $unit) use ($guild, $parser) {
            collect($data)->each(function($member_data) use ($guild, $unit, $parser) {
                DB::transaction(function() use ($guild, $member_data, $unit, $parser) {
                    if (!isset($member_data['url'])) { return; }
                    $member = Member::firstOrNew(['url' => $member_data['url']]);

                    $member->url = $member_data['url'];
                    $member->player = $member_data['player'];

                    $gp = $parser->memberGP()[$member->url];
                    $member->gp = $gp['gp'];
                    $member->character_gp = $gp['character_gp'];
                    $member->ship_gp = $gp['ship_gp'];

                    $member->guild()->associate($guild);
                    $member->save();

                    $character = Character::firstOrNew([
                        'member_id' => $member->id,
                        'unit_name' => $unit,
                    ]);

                    $character->gear_level = $member_data['gear_level'];
                    $character->power = $member_data['power'];
                    $character->level = $member_data['level'];
                    $character->combat_type = $member_data['combat_type'];
                    $character->rarity = $member_data['rarity'];

                    $character->member()->associate($member);
                    $character->save();

                    $memberZetas = $parser->zetas()[$member->url];
                    if (isset($memberZetas) && isset($memberZetas[$character->unit_name])) {
                        $zetas = $memberZetas[$character->unit_name];

                        $ids = Zeta::where('character_id', $character->unit_name)
                            ->whereIn('name', $zetas)
                            ->get()->pluck('id')->all();

                        $character->zetas()->sync($ids);
                    }
                });
            });
        });

        return 0;
    }
}
