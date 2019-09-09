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

class ProcessGuildAlly implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $allyCode;

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
    public function __construct($allyCode)
    {
        $this->allyCode = preg_replace('/[^0-9]/', '', $allyCode);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('swgoh:guild', [
            '--ally',
            'guild' => $this->allyCode
        ]);

        broadcast(new GuildProcessed(
            Member::firstOrFail(['ally_code' => $this->allyCode])->guild
        ));
    }

    public function tags() {
        return ['guild', 'ally_code:' . $this->allyCode];
    }
}
