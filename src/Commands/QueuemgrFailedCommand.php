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
            $class = get_class($entry->getJob());
            $this->info("{$entry->created_at->format('Ymd.Hi')} | ID:{$entry->getId()} | {$class}");
        }
    }
}