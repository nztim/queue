<?php

namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueuedJob\QueuedJobRepo;

class DumpCommand extends Command
{
    protected $signature = 'qm:dump {id}';
    protected $description = 'Shows the content of the specified job';

    public function handle()
    {
        $id = $this->argument('id');
        $job = app(QueuedJobRepo::class)->findById($id);
        if (!$job) {
            $this->warn("Job id:{$id} not found");
            return;
        }
        $command = $job->command();
        $type = is_object($command) ? get_class($command) : gettype($command);
        $rows = [
            ['id', $job->id()],
            ['type', $type],
            ['attempts', $job->attempts()],
            ['created', $job->created()->format('j M Y')],
            ['updated', $job->updated()->format('j M Y')],
            ['completed', is_null($job->completed()) ? '' : $job->completed()->format('j M Y')],
        ];
        $this->table([], $rows);
        $this->info(var_export($command, true));
    }
}
