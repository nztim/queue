<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use QueueMgr;

class ProcessCommand extends Command
{
    protected $name = 'queuemgr:process';

    protected $description = 'Processes all Queue Manager queued jobs';

    public function handle()
    {
        QueueMgr::process();
    }
}
