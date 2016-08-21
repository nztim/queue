<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueuedJob\QueuedJob;
use NZTim\Queue\QueueManager;

class ListCommand extends Command
{
    protected $signature = 'queuemgr:list {days=7}';

    protected $description = 'Lists recent jobs within the specified number of days';

    /** @var QueueManager */
    protected $queueManager;

    public function __construct(QueueManager $queueManager)
    {
        parent::__construct();
        $this->queueManager = $queueManager;
    }

    public function handle()
    {
        $days = $this->argument('days');
        $entries = $this->queueManager->recent($days);
        $jobs = [];
        foreach($entries as $entry) {
            /** @var QueuedJob $entry */
            $job['Created'] = $entry->created_at->format('Y-m-d - H:i');
            $job['ID'] =  "ID:{$entry->getId()}";
            $job['Class'] = get_class($entry->getJob());
            $job['Status'] = is_null($entry->deleted_at) ? "Incomplete" : "Complete";
            $job['Status'] .= " ({$entry->attempts})";
            if ($entry->attempts == 0) {
                $job['Status'] = "Failed!!!";
            }
            $jobs[] = $job;
        }
        if (count($jobs)) {
            $this->table(array_keys($jobs[0]), $jobs);
        } else {
            $this->info('No jobs found');
        }
    }
}
