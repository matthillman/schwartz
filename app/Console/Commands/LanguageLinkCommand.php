<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LanguageLinkCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'language:link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a symbolic link from "resources/lang" to "storage/app/lang"';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (file_exists(resource_path('lang'))) {
            return $this->error('The "resources/lang" directory already exists.');
        }

        $this->laravel->make('files')->link(
            storage_path('app/lang'), resource_path('lang')
        );

        $this->info('The [resources/lang] directory has been linked.');
    }
}
