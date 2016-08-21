<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use QueueMgr;

class Daemon extends Command
{
    protected $signature = 'queuemgr:daemon {seconds=50}';
    protected $description = 'Runs process command repeatedly for the period required';

    public function handle()
    {
        $seconds = intval($this->argument('seconds'));
        QueueMgr::daemon($seconds);
    }
}
