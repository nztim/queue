<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueuedJob\QueuedJob;

class Retry extends Command
{
    protected $signature = 'queuemgr:retry {id}';
    protected $description = 'Retries the specified job';

    public function handle()
    {
        $id = $this->argument('id');
        $job = (new QueuedJob)->where('attempts', 0)->where('id', $id)->first();
        /** @var QueuedJob $job */
        if (!$job) {
            $this->warn("Job id:{$id} not found");
            return;
        }
        $job->attempts = 1;
        $job->save();
        $this->info('Job will be retried, see queuemgr:list for status');
    }
}
