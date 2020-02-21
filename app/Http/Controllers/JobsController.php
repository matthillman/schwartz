<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Horizon\Contracts\JobRepository;

class JobsController extends Controller
{
    /**
     * The job repository implementation.
     *
     * @var \Laravel\Horizon\Contracts\JobRepository
     */
    public $jobs;

    /**
     * Create a new controller instance.
     *
     * @param  \Laravel\Horizon\Contracts\JobRepository  $jobs
     * @return void
     */
    public function __construct(JobRepository $jobs)
    {
        $this->jobs = $jobs;
    }

    /**
     * Get all of the recent jobs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function index(Request $request)
    {
        $jobs = $this->jobs->getRecent($request->query('starting_at', -1))->map(function ($job) {
            return $this->decode($job);
        })->values();

        return [
            'jobs' => $jobs,
            'total' => $this->jobs->countRecent(),
        ];
    }

    /**
     * Get the details of a recent job by ID.
     *
     * @param  string  $id
     * @return array
     */
    public function show($id)
    {
        return (array) $this->jobs->getJobs([$id])->map(function ($job) {
            return $this->decode($job);
        })->first();
    }

    public function jobsForTag(Request $request) {
        $jobs = $this->jobs->getRecent($request->query('starting_at', -1))
            ->sortBy('id')
            ->map(function ($job) {
                return $this->decode($job);
            })
            ->filter(function ($job) use ($request) {
                return array_reduce(explode(',', $request->tags), function($c, $tag) use ($job) {
                    return $c && in_array($tag, $job->payload->tags);
                }, true);
            })
            ->values();

        return response()->json($jobs);
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
