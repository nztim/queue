<?php

namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueueManager;

class ClearFailedCommand extends Command
{
    protected $name = 'qm:clear';

    protected $description = 'Clears all Queue Manager failed jobs';

    public function handle()
    {
        app(QueueManager::class)->clearFailed();
        $this->info('All failed jobs cleared');
    }
}
