<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueuedJob\QueuedJob;
use QueueMgr;

class FailedCommand extends Command
{
    protected $name = 'queuemgr:failed';

    protected $description = 'Lists failed jobs';

    public function handle()
    {
        $entries = QueueMgr::allFailed();
        foreach($entries as $entry) {
            /** @var QueuedJob $entry */
            $job = $entry->getJob();
            $class = get_class($job);
            $this->info("{$entry->created_at->format('Y-m-d @ H:i')} | ID:{$entry->getId()} | {$class}");
        }
    }
}
