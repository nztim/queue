<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use NZTim\Queue\QueueManager;

class ListCommand extends Command
{
    protected $signature = 'queuemgr:list {days=7}';

    protected $description = 'Lists recent jobs within the specified number of days';

    /** @var QueueManager */
    protected $queueManager;

    public function __construct(QueueManager $queueManager)
    {
        parent::__construct();
        $this->queueManager = $queueManager;
    }

    public function handle()
    {
        $days = $this->argument('days');
        $entries = $this->queueManager->recent($days);
        foreach($entries as $entry) {
            $message = $entry->created_at->format('Y-m-d @ H:i') . ' | ';
            $message .= "ID:{$entry->getId()} | ";
            $message .= get_class($entry->getJob()) . ' | ';
            $status = "Complete";
            $method = "info";
            if (is_null($entry->deleted_at)) {
                $status = "Incomplete";
                $method = "error";
            }
            $status .= " ({$entry->attempts})";
            if ($entry->attempts == 0) {
                $status = "Failed!";
                $method = 'error';
            }
            $message .= $status;
            $this->$method($message);
        }
    }
}
