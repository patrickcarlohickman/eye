<?php

namespace Eyewitness\Eye\Queue;

use Exception;
use Throwable;
use Illuminate\Queue\Worker as OriginalWorker;
use Illuminate\Queue\WorkerOptions;

class Worker extends OriginalWorker
{
    use WorkerTrait;

    /**
     * We wrap the standard job processor, and add our tracking code around it.
     *
     * @param  string  $connectionName
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  \Illuminate\Queue\WorkerOptions  $options
     * @return void
     *
     * @throws \Throwable
     */
    public function process($connectionName, $job, WorkerOptions $options)
    {
        $startTime = microtime(true);

        try {
            parent::process($connectionName, $job, $options);
        } catch (Exception $e) {
            $this->recordJobException($e);
        } catch (Throwable $e) {
            $this->recordJobException($e);
        } finally {
            $this->recordJobEnd($startTime, $job->resolveName());
        }
    }
}
