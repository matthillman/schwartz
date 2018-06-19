<?php

namespace App\Jobs;

use Artisan;
use App\Guild;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessSchwartzGuilds implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $guild;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Guild $guild)
    {
        $this->guild = $guild;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('pull:guild', [
            'guild' => $this->guild->guild_id
        ]);
    }
}
