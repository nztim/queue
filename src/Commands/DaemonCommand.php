<?php

namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueueManager;

class DaemonCommand extends Command
{
    protected $signature = 'qm:daemon {seconds=50}';
    protected $description = 'Runs process command repeatedly for the period required';

    public function handle()
    {
        $seconds = intval($this->argument('seconds'));
        app(QueueManager::class)->daemon($seconds);
    }
}
