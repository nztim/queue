<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use QueueMgr;

class ClearFailedCommand extends Command
{
    protected $name = 'queuemgr:clear';

    protected $description = 'Clears all Queue Manager failed jobs';

    public function handle()
    {
        QueueMgr::clearFailed();
        $this->info('All failed jobs cleared');
    }
}
