<?php

namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueueManager;

class StatusCommand extends Command
{
    protected $signature = 'qm:status {hours=24}';
    protected $description = 'Displays queue status over the specified period (default 24 hours)';

    public function handle()
    {
        $hours = intval($this->argument('hours'));
        $this->info(app(QueueManager::class)->status($hours));
    }
}
