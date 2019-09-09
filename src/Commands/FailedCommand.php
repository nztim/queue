<?php

namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueueManager;

class FailedCommand extends Command
{
    protected $name = 'qm:failed';
    protected $description = 'Lists failed jobs';

    public function handle()
    {
        $jobs = app(QueueManager::class)->allFailed();
        $table = app(MakeTable::class)->fromJobs($jobs);
        if (count($table)) {
            $this->table(array_keys($table[0]), $table);
        } else {
            $this->info('No failed jobs found');
        }
    }
}
