<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use QueueMgr;

class LogStatus extends Command
{
    protected $signature = 'queuemgr:logstatus {hours=24}';
    protected $description = 'Logs queue status over the specified period (default 24 hours)';

    public function handle()
    {
        $hours = intval($this->argument('hours'));
        QueueMgr::logStatus($hours);
    }
}
