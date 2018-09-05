<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueuedJob\QueuedJob;

class Dump extends Command
{
    protected $signature = 'queuemgr:dump {id}';
    protected $description = 'Shows the content of the specified job';

    public function handle()
    {
        $id = $this->argument('id');
        $job = (new QueuedJob)->withTrashed()->find($id);
        /** @var QueuedJob $job */
        if (!$job) {
            $this->warn("Job id:{$id} not found");
            return;
        }
        $jobAttr = $job->getJob();
        $type = is_object($jobAttr) ? get_class($jobAttr) : gettype($jobAttr);
        $rows = [
            ['id', $job->getId()],
            ['type', $type],
            ['attempts', $job->attempts],
            ['created', $job->created_at->format('j M Y')],
            ['updated', $job->updated_at->format('j M Y')],
            ['deleted', is_null($job->deleted_at) ? '' : $job->deleted_at->format('j M Y')],
        ];
        $this->table([], $rows);
        $this->info(var_export($job->getJob(), true));
    }
}
