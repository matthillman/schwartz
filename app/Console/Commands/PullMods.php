<?php

namespace App\Console\Commands;

use App\Mods\ModsParser;
use Illuminate\Console\Command;

class PullMods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mods:pull {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull a user‘s mods from swogh.gg';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $parser = new ModsParser($this->argument('user'));
        $parser->scrape();

        echo $parser->mods->toJson();

        return 0;
    }
}
