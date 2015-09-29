<?php namespace NZTim\Queue;

use Carbon\Carbon;
use Exception;
use Log;
use NZTim\Queue\QueuedJob\QueuedJob;
use NZTim\Queue\QueuedJob\QueuedJobRepository;

class QueueManager
{
    protected $attempts;
    protected $queuedJobRepo;

    public function __construct(QueuedJobRepository $queuedJobRepo)
    {
        $this->attempts = env('QUEUEMGR_ATTEMPTS', 5);
        $this->queuedJobRepo = $queuedJobRepo;
    }

    public function add(Job $job)
    {
        $queuedJob = QueuedJob::newJob($job, $this->attempts);
        $this->queuedJobRepo->persist($queuedJob);
    }

    public function process()
    {
        $this->queuedJobRepo->purgeDeleted();
        $queue = $this->queuedJobRepo->allOutstanding();
        foreach($queue as $item) {
            /** @var QueuedJob $item */
            try {
                $item->getJob()->handle();
                $this->queuedJobRepo->delete($item);
            } catch (Exception $e) {
                $this->handleException($item, $e);
            }
        }
    }

    protected function handleException(QueuedJob $item, Exception $e)
    {
        $class = (new \ReflectionClass($e))->getShortName();
        Log::warning("Exception executing job ID:{$item->getId()}: {$class} | {$e->getMessage()}");
        $item->decrementAttempts();
        $this->queuedJobRepo->persist($item);
        if($item->failed()) {
            $class = get_class($item->getJob());
            Log::error("Job ID:{$item->getId()} ({$class}) has failed and will not be retried.");
        }
    }

    public function allFailed()
    {
        return $this->queuedJobRepo->allFailed();
    }

    public function clearFailed()
    {
        $this->queuedJobRepo->clearFailed();
    }

    public function check()
    {
        $queue = $this->queuedJobRepo->allOutstanding();
        $maxAgeInHours = env('QUEUEMGR_MAX_AGE', 1);
        $maxAge = Carbon::now()->subHours($maxAgeInHours);
        $exceeded = false;
        foreach ($queue as $job) {
            /** @var QueuedJob $job */
            if (!$job->failed() && $job->created_at < $maxAge) {
                $exceeded = true;
            }
        }
        if ($exceeded) {
            Log::error("QueueMgr jobs queue has exceeded the maximum age ({$maxAgeInHours} hours). There may be a server problem, such as the mutex file not being cleared.");
        }
    }
}