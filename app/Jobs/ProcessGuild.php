<?php

namespace App\Jobs;

use Artisan;
use App\Guild;
use Illuminate\Bus\Queueable;
use App\Events\GuildProcessed;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessGuild implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $guild;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 60 * 10;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($guild)
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
        Artisan::call('swgoh:guild', [
            'guild' => $this->guild
        ]);

        broadcast(new GuildProcessed(
            Guild::where(['guild_id' => $this->guild])->firstOrFail()
        ));
    }

    public function tags() {
        return ['guild', 'guild_id:' . $this->guild];
    }
}
