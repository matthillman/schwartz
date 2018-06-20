<?php

namespace App\Console\Commands;

use DB;
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

        $units->each(function($data, $unit) use ($guild) {
            collect($data)->each(function($member_data) use ($guild, $unit) {
                DB::transaction(function() use ($guild, $member_data, $unit) {
                    if (!isset($member_data['url'])) { return; }
                    $member = Member::firstOrNew(['url' => $member_data['url']]);

                    $member->url = $member_data['url'];
                    $member->player = $member_data['player'];

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
                });
            });
        });

        return 0;
    }
}
