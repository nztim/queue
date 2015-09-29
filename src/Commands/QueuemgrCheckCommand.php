<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use QueueMgr;

class QueuemgrCheckCommand extends Command
{
    protected $name = 'queuemgr:check';

    protected $description = 'Logs an error if unprocessed jobs exceed a specified age';

    public function handle()
    {
        QueueMgr::check();
    }
}