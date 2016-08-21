<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use QueueMgr;

class Pause extends Command
{
    protected $signature = 'queuemgr:pause {minutes=10}';
    protected $description = 'Pauses queue processing for period specified (or cache is cleared)';

    public function handle()
    {
        $minutes = intval($this->argument('minutes'));
        if (!QueueMgr::pause($minutes)) {
            $this->info('Currently executing, waiting for jobs to finish.');
            while (!QueueMgr::pause($minutes)) {
                $this->output->write('<info>.</info>');
                sleep(2);
            }
        }
        $this->info('Queue processing is now paused for ' . $minutes . ' minutes (or until cache is cleared)');
    }
}
