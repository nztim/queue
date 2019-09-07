<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueueManager;

class FailedCommand extends Command
{
    protected $name = 'queuemgr:failed';
    protected $description = 'Lists failed jobs';
    protected $listJobs;

    public function __construct(ListJobs $listJobs)
    {
        $this->listJobs = $listJobs;
        parent::__construct();
    }

    public function handle()
    {
        $entries = app(QueueManager::class)->allFailed();
        $jobs = $this->listJobs->table($entries);
        if (count($jobs)) {
            $this->table(array_keys($jobs[0]), $jobs);
        } else {
            $this->info('No failed jobs found');
        }
    }
}
