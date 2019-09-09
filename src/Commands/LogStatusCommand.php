<?php

namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueueManager;

class LogStatusCommand extends Command
{
    protected $signature = 'qm:logstatus {hours=24}';
    protected $description = 'Logs queue status over the specified period (default 24 hours)';

    public function handle()
    {
        $hours = intval($this->argument('hours'));
        app(QueueManager::class)->logStatus($hours);
    }
}
