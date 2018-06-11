<?php

namespace Eyewitness\Eye\Queue;

use Exception;
use Throwable;
use Illuminate\Queue\Worker as OriginalWorker;
use Illuminate\Contracts\Queue\Job;

class WorkerLegacy extends OriginalWorker
{
    use WorkerTrait;

    /**
     * We wrap the standard job processor, and add our tracking code around it.
     *
     * @param  string  $connection
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  int  $maxTries
     * @param  int  $delay
     * @return array|null
     *
     * @throws \Throwable
     */
    public function process($connection, Job $job, $maxTries = 0, $delay = 0)
    {
        $startTime = microtime(true);
        $result = null;

        try {
            $result = parent::process($connection, $job, $maxTries, $delay);
        } catch (Exception $e) {
            $this->recordJobException($e);
        } catch (Throwable $e) {
            $this->recordJobException($e);
        } finally {
            $this->recordJobEnd($startTime, $job->resolveName());
            return $result;
        }
    }
}
