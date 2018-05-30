<?php

namespace Eyewitness\Eye\App\Queue;

use Eyewitness\Eye\Eye;

trait WorkerTrait
{
    /**
     * The current queue connection.
     *
     * @var string
     */
    public $eyeConnection;

    /**
     * The current tube being processed.
     *
     * @var string
     */
    public $eyeTube;

    /**
     * The list of all tubes being processed.
     *
     * @var string
     */
    public $eyeQueues;

    /**
     * Extend the worker and place a heartbeat as it is processing. Then
     * simply "feed" the tubes into the next job parent. This provides
     * the exact same functionality, but this way we know exactly
     * which tube is processing the job.
     *
     * @param  \Illuminate\Contracts\Queue\Queue  $connection
     * @param  string  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    protected function getNextJob($connection, $queue)
    {
        if ($this->cache) {
            foreach ($this->eyeQueues as $tube) {
                $this->eyewitnessHeartBeat($tube);
            }
        }

        foreach ($this->eyeQueues as $tube) {
            $job = parent::getNextJob($connection, $tube);

            if (! is_null($job)) {
                $this->eyeTube = $tube;
                return $job;
            }
        }
    }

    /**
     * Check if we have recently pinged Eyewitness for this queue. Only
     * ping if the cache has expired.
     *
     * @param  string  $tube
     * @return void
     */
    protected function eyewitnessHeartBeat($tube)
    {
        if (! $this->cache->has('eyewitness_queue_heartbeat_'.$this->eyeConnection.'_'.$tube)) {
            $this->cache->add('eyewitness_queue_heartbeat_'.$this->eyeConnection.'_'.$tube, 1, 6);
            app(Eye::class)->api()->sendQueuePing($this->eyeConnection, $tube);
        }
    }

    /**
     * Record the end of the job details.
     *
     * @param  float  $startTime
     * @return void
     */
    public function recordJobEnd($startTime)
    {
        $endTime = round((microtime(true) - $startTime)*1000);

        $this->cache->add('eyewitness_q_process_time_'.$this->eyeConnection.'_'.$this->eyeTube, 0, 180);
        $this->cache->increment('eyewitness_q_process_time_'.$this->eyeConnection.'_'.$this->eyeTube, $endTime);
        $this->cache->add('eyewitness_q_process_count_'.$this->eyeConnection.'_'.$this->eyeTube, 0, 180);
        $this->cache->increment('eyewitness_q_process_count_'.$this->eyeConnection.'_'.$this->eyeTube);
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
        $this->cache->add('eyewitness_q_exception_count_'.$this->eyeConnection.'_'.$this->eyeTube, 0, 180);
        $this->cache->increment('eyewitness_q_exception_count_'.$this->eyeConnection.'_'.$this->eyeTube);

        throw $exception;
    }

    /**
     * Capture how long a worker is sleeping for. We need to cycle all tubes this worker check to
     * give each of them credit for the sleep. This allows us to handle different worker configurations
     * and should cover all situations.
     *
     * @param  int   $seconds
     * @return void
     */
    public function sleep($seconds)
    {
        if (is_array($this->eyeQueues)) {
            foreach ($this->eyeQueues as $tube) {
                $this->cache->add('eyewitness_q_idle_time_'.$this->eyeConnection.'_'.$tube, 0, 180);
                $this->cache->increment('eyewitness_q_idle_time_'.$this->eyeConnection.'_'.$tube, $seconds);
            }
        }

        parent::sleep($seconds);
    }
}
