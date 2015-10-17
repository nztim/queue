<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use QueueMgr;

class QueuemgrFailedCommand extends Command
{
    protected $name = 'queuemgr:failed';

    protected $description = 'Lists failed jobs';

    public function handle()
    {
        $entries = QueueMgr::allFailed();
        foreach($entries as $entry) {
            $job = $entry->getJob();
            $class = get_class($job);
            $this->info("{$entry->created_at->format('Y-m-d @ H:i')} | ID:{$entry->getId()} | {$class}");
        }
    }
}