<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueueManager;

class ListCommand extends Command
{
    protected $signature = 'queuemgr:list {days=7}';

    protected $description = 'Lists recent jobs within the specified number of days';

    /** @var QueueManager */
    protected $queueManager;
    protected $listJobs;

    public function __construct(QueueManager $queueManager, ListJobs $listJobs)
    {
        $this->queueManager = $queueManager;
        $this->listJobs = $listJobs;
        parent::__construct();
    }

    public function handle()
    {
        $days = $this->argument('days');
        $entries = $this->queueManager->recent($days);
        $jobs = $this->listJobs->table($entries);
        if (count($jobs)) {
            $this->table(array_keys($jobs[0]), $jobs);
        } else {
            $this->info('No jobs found');
        }
    }
}
