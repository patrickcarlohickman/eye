<?php

namespace Eyewitness\Eye\Queue;

use Eyewitness\Eye\Eye;

trait WorkerTrait
{
    /**
     * The list of all queues being processed.
     *
     * @var array
     */
    public $eyeQueues;

    /**
     * The current tube being processed.
     *
     * @var string
     */
    public $currentQueue;

    /**
     * Extend the worker and place a heartbeat as it is processing. Then simply "feed"
     * the tubes into the next job parent. This provides the exact same functionality,
     * but this way we know exactly which tube is processing the job.
     *
     * @param  \Illuminate\Contracts\Queue\Queue  $connection
     * @param  string  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    protected function getNextJob($connection, $queue)
    {
        if ($this->cache) {
            foreach ($this->eyeQueues as $queue) {
                $this->eyewitnessHeartbeat($queue);
            }
        }

        foreach ($this->eyeQueues as $queue) {
            $this->currentQueue = $queue;

            $job = parent::getNextJob($connection, $queue->tube);

            if (! is_null($job)) {
                $this->eyeTube = $queue->tube;
                return $job;
            }
        }
    }

    /**
     * Check if we have stored a recent heartbeart. Only update the database if the cache has
     * expired.
     *
     * @param  \Eyewitness\Eye\Repo\Queue  $queue
     * @return void
     */
    protected function eyewitnessHeartbeat($queue)
    {
        if (! $this->cache->has('eyewitness_q_heartbeat_'.$queue->id)) {
            $this->cache->add('eyewitness_q_heartbeat_'.$queue->id, 1, 1);
            $queue->heartbeat();
        }
    }

    /**
     * Record the end of the job details. Ignore the sonar job from artifically inflating
     * the queue counters.
     *
     * @param  float  $startTime
     * @param  string  $jobName
     * @return void
     */
    public function recordJobEnd($startTime, $jobName)
    {
        $endTime = round((microtime(true) - $startTime)*1000);

        $this->cache->add('eyewitness_q_process_time_'.$this->currentQueue->id, 0, 180);
        $this->cache->increment('eyewitness_q_process_time_'.$this->currentQueue->id, $endTime);

        if ($jobName !== "Eyewitness\\Eye\\Queue\\Jobs\\Sonar") {
            $this->cache->add('eyewitness_q_process_count_'.$this->currentQueue->id, 0, 180);
            $this->cache->increment('eyewitness_q_process_count_'.$this->currentQueue->id);
        }
    }

    /**
     * Record the exeception count.
     *
     * @param  string  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function recordJobException($exception)
    {
        $this->cache->add('eyewitness_q_exception_count_'.$this->currentQueue->id, 0, 180);
        $this->cache->increment('eyewitness_q_exception_count_'.$this->currentQueue->id);

        throw $exception;
    }

    /**
     * Capture how long a worker is sleeping for. We need to cycle all tubes this worker checks to
     * give each of them credit for the sleep. This allows us to handle different worker configurations
     * and should cover all situations.
     *
     * @param  int|float  $seconds
     * @return void
     */
    public function sleep($seconds)
    {
        foreach ($this->eyeQueues as $queue) {
            $this->cache->add('eyewitness_q_idle_time_'.$queue->id, 0, 180);

            if ($seconds < 1) {
                $this->cache->increment('eyewitness_q_idle_time_'.$queue->id, 1);
            } else {
                $this->cache->increment('eyewitness_q_idle_time_'.$queue->id, round($seconds));
            }
        }

        parent::sleep($seconds);
    }
}
