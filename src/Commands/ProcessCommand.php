<?php

namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueueManager;

class ProcessCommand extends Command
{
    protected $name = 'qm:process';

    protected $description = 'Processes all Queue Manager jobs';

    public function handle()
    {
        app(QueueManager::class)->process();
    }
}
