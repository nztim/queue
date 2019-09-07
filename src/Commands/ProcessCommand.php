<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueueManager;

class ProcessCommand extends Command
{
    protected $name = 'queuemgr:process';

    protected $description = 'Processes all Queue Manager queued jobs';

    public function handle()
    {
        app(QueueManager::class)->process();
    }
}
