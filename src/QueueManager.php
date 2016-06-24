<?php namespace NZTim\Queue;

use Carbon\Carbon;
use Illuminate\Cache\Repository;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Collection;
use Log;
use NZTim\Queue\QueuedJob\QueuedJob;
use NZTim\Queue\QueuedJob\QueuedJobRepository;
use Throwable;

class QueueManager
{
    protected $attempts;
    protected $cache;
    protected $queuedJobRepo;
    protected $mutexHandler;

    public function __construct(QueuedJobRepository $queuedJobRepo, Repository $cache)
    {
        $this->attempts = env('QUEUEMGR_ATTEMPTS', 5);
        $this->queuedJobRepo = $queuedJobRepo;
        $this->cache = $cache;
    }

    // Facade commands --------------------------------------------------------

    public function add(Job $job)
    {
        $job = $this->queuedJobRepo->newInstance($job, $this->attempts);
        $this->queuedJobRepo->persist($job);
    }

    // Console commands -------------------------------------------------------

    public function process()
    {
        $cacheKey = 'nztim-queuemgr-lock';
        if($this->cache->has($cacheKey)) {
            Log::warning("QueueMgr triggered but process already running");
            return;
        }
        $this->cache->put($cacheKey, true, 60);
        $this->queuedJobRepo->purgeDeleted();
        $this->executeJobs();
        $this->cache->forget($cacheKey);
    }

    protected function executeJobs()
    {
        try {
            $queue = $this->queuedJobRepo->allOutstanding();
        } catch (Throwable $e) {
            Log::error("QueueMgr error accessing db: " . $e->getMessage());
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
}
