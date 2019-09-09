<?php

namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueueManager;

class ResumeCommand extends Command
{
    protected $signature = 'qm:resume';
    protected $description = 'Resume paused queue processing';

    public function handle()
    {
        if (!app(QueueManager::class)->resume()) {
            $this->info('Queue processing was not paused - no action taken');
            return 1;
        }
        $this->info('Queue processing resumed');
        return 0;
    }
}
