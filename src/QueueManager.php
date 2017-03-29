<?php namespace NZTim\Queue;

use Illuminate\Support\Collection;
use Log;
use NZTim\Queue\QueuedJob\QueuedJob;
use NZTim\Queue\QueuedJob\QueuedJobRepository;
use Throwable;

class QueueManager
{
    protected $attempts;
    protected $lock;
    protected $queuedJobRepo;
    protected $errorTimeoutMinutes;
    protected $secondsBetweenAttempts = 10;

    public function __construct(QueuedJobRepository $queuedJobRepo, Lock $lock)
    {
        $this->errorTimeoutMinutes = env('QUEUEMGR_TIMEOUT', 20);
        $this->attempts = env('QUEUEMGR_ATTEMPTS', 5);
        $this->queuedJobRepo = $queuedJobRepo;
        $this->lock = $lock;
    }

    public function add(Job $job)
    {
        $job = $this->queuedJobRepo->newInstance($job, $this->attempts);
        $this->queuedJobRepo->persist($job);
    }

    public function process()
    {
        if (!$this->lock->set($this->errorTimeoutMinutes)) {
            info('QueueMgr triggered but process already running');
            return;
        }
        $this->executeJobs();
        $this->lock->clear();
    }

    /**
     * @param int $runtimeSeconds in seconds
     */
    public function daemon(int $runtimeSeconds = 0)
    {
        $timeoutMinutes = intval(ceil($runtimeSeconds / 60)) + $this->errorTimeoutMinutes;
        if (!$this->lock->set($timeoutMinutes)) {
            info('QueueMgr triggered but process already running');
            return;
        }
        $start = time();
        while ((time() - $start) < $runtimeSeconds) {
            $this->executeJobs();
            sleep($this->secondsBetweenAttempts);
        }
        $this->lock->clear();
    }

    protected function executeJobs()
    {
        try {
            $this->queuedJobRepo->purgeDeleted();
            $queue = $this->queuedJobRepo->allOutstanding();
        } catch (Throwable $e) {
            Log::error("QueueMgr error accessing database: " . $e->getMessage());
            return;
        }
        foreach($queue as $item) {
            /** @var QueuedJob $item */
            try {
                $item->getJob()->handle();
                $this->queuedJobRepo->delete($item);
            } catch (Throwable $e) {
                $this->handleException($item, $e);
            }
        }
    }

    protected function handleException(QueuedJob $item, Throwable $e)
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

    /**
     * @param Integer $days
     * @return Collection
     */
    public function recent($days)
    {
        return $this->queuedJobRepo->recent(intval($days));
    }

    public function allFailed()
    {
        return $this->queuedJobRepo->allFailed();
    }

    public function clearFailed()
    {
        $this->queuedJobRepo->clearFailed();
    }

    public function pause(int $minutes = 10) : bool
    {
        return $this->lock->pause($minutes);
    }

    public function resume() : bool
    {
        return $this->lock->resume();
    }

    public function status(int $hours = 24) : string
    {
        $outstanding = $this->queuedJobRepo->allOutstanding()->count();
        $failed = $this->queuedJobRepo->allFailed()->count();
        $completed = $this->queuedJobRepo->completed($hours);
        $completedCount = $completed->count();
        $totalTime = $completed->reduce(function($total, QueuedJob $job) {
            return $total + $job->processingTime();
        }, 0);
        $avgTime = $completedCount ? number_format($totalTime / $completedCount, 1) : 0;
        return "QueueMgr: {$outstanding} outstanding, {$failed} failed, {$completedCount} completed, avg {$avgTime} seconds/job (last {$hours} hours)";
    }

    public function logStatus(int $hours = 24)
    {
        Log::info($this->status($hours));
    }
}
