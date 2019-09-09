<?php

namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueueManager;

class PauseCommand extends Command
{
    protected $signature = 'qm:pause {minutes=10}';
    protected $description = 'Pauses queue processing for period specified';

    public function handle()
    {
        $minutes = intval($this->argument('minutes'));
        if (!app(QueueManager::class)->pause($minutes)) {
            $this->info('Currently executing, waiting for jobs to finish.');
            while (!app(QueueManager::class)->pause($minutes)) {
                $this->output->write('<info>.</info>');
                sleep(2);
            }
        }
        $this->info('Queue processing is now paused for ' . $minutes . ' minutes');
    }
}
