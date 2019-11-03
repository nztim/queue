<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\OutputStyle;
use NZTim\Queue\QueuedJob\QueuedJob;
use Throwable;

class Dump
{
    private $output;

    public function __construct(OutputStyle $output)
    {
        $this->output = $output;
    }

    public function toOutput(QueuedJob $job)
    {
        try {
            $decoded = base64_decode($job->rawCommand());
        } catch (Throwable $e) {
            $decoded = "(Unable to decode)";
        }
        try {
            $unserialized = var_export(unserialize($decoded), true);
        } catch (Throwable $e) {
            $unserialized = "(Unable to unserialize)";
        }
        $this->info("id:" . $job->id());
        $this->info("command:" . $job->rawCommand());

        $this->info("attempts:" . $job->attempts());
        $this->info("created:" . $job->created()->format('j M Y'));
        $this->info("updated:" . $job->updated()->format('j M Y'));
        $this->info("completed:" . ($job->completed() ? $job->completed()->format('j M Y') : "null"));
        $this->info("decoded:" . $decoded);
        $this->info("unserialized:");
        $this->info($unserialized);
    }

    protected function info(string $line): void
    {
        $this->output->writeln('<info>' . $line . '</info>');
    }
}
