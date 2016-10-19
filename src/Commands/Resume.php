<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use QueueMgr;

class Resume extends Command
{
        protected $signature = 'queuemgr:resume';
        protected $description = 'Resume paused queue processing';

    public function handle()
    {
        if (!QueueMgr::resume()) {
            $this->info('Queue processing was not paused - no action taken');
            return 1;
        }
        $this->info('Queue processing resumed');
        return 0;
    }
}
