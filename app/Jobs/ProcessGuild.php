<?php

namespace App\Jobs;

use Redis;
use Artisan;
use App\Guild;
use Illuminate\Bus\Queueable;
use App\Events\GuildProcessed;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Laravel\Horizon\Contracts\JobRepository;

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
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 20;

    /**
     * The maximum number of exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 1;

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
    public function handle(JobRepository $jobs)
    {
        $hasJobScheduled = $this->jobs->getRecent(-1)
            ->sortBy('id')
            ->map(function ($job) {
                return $this->decode($job);
            })
            ->filter(function ($job) {
                return in_array('guild_id:' . $this->guild, collect($job->payload->tags)->values()->all());
            })
            ->isNotEmpty();

        if ($hasJobScheduled) {
            return;
        }

        Redis::throttle(config('app.host') . '-guild')->allow(2)->every(5 * 60)->then(function() {
            Artisan::call('swgoh:guild', [
                'guild' => $this->guild
            ]);

            broadcast(new GuildProcessed(
                Guild::where(['guild_id' => $this->guild])->firstOrFail()
            ));
        }, function() {
            return $this->release(30);
        });
    }

    public function tags() {
        return ['guild', 'guild_id:' . $this->guild];
    }

    /**
     * Decode the given job.
     *
     * @param  object  $job
     * @return object
     */
    protected function decode($job)
    {
        $job->payload = json_decode($job->payload);

        return $job;
    }
}
