<?php

namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueuedJob\QueuedJobRepo;

class RetryCommand extends Command
{
    protected $signature = 'qm:retry {id}';
    protected $description = 'Retries the specified job';

    public function handle()
    {
        $id = $this->argument('id');
        $job = app(QueuedJobRepo::class)->findById($id);
        if (!$job) {
            $this->warn("Job id:{$id} not found");
            return;
        }
        if (!$job->failed()) {
            $this->warn('Job has not failed! No change made.');
            return;
        }
        $job->retry();
        app(QueuedJobRepo::class)->persist($job);
        $this->info('Job will be retried, see qm:list for status');
    }
}
