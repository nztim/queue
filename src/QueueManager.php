<?php namespace NZTim\Queue;

use Psr\Log\LoggerInterface;
use Illuminate\Support\Collection;
use NZTim\CommandBus\CommandBus;
use NZTim\Queue\QueuedJob\QueuedJob;
use NZTim\Queue\QueuedJob\QueuedJobRepo;
use ReflectionClass;
use Throwable;

class QueueManager
{
    private CommandBus $bus;
    private QueuedJobRepo $repo;
    private Lock $lock;
    private LoggerInterface $logger;
    private int $timeoutMinutes;

    public function __construct(CommandBus $bus, QueuedJobRepo $repo, Lock $lock, LoggerInterface $logger, int $timeoutMinutes = 20)
    {
        $this->bus = $bus;
        $this->repo = $repo;
        $this->lock = $lock;
        $this->logger = $logger;
        $this->timeoutMinutes = $timeoutMinutes;
    }

    public function add(object $command): void
    {
        $job = QueuedJob::fromCommand($command);
        $this->repo->persist($job);
    }

    public function process(): void
    {
        if (!$this->lock->set($this->timeoutMinutes)) {
            $this->logger->info('QueueMgr triggered but process already running');
            return;
        }
        $this->executeJobs();
        $this->lock->clear();
    }

    public function daemon(int $runtimeSeconds = 0, int $secondsBetweenAttempts = 5): void
    {
        $timeoutMinutes = intval(ceil($runtimeSeconds / 60)) + $this->timeoutMinutes;
        if (!$this->lock->set($timeoutMinutes)) {
            $this->logger->info('QueueMgr triggered but process already running');
            return;
        }
        $start = time();
        while ((time() - $start) < $runtimeSeconds) {
            $this->executeJobs();
            sleep($secondsBetweenAttempts);
        }
        $this->lock->clear();
    }

    private function executeJobs()
    {
        try {
            $this->repo->purgeCompleted();
            $queue = $this->repo->outstanding();
        } catch (Throwable $e) {
            $this->logger->error("QueueMgr error accessing database: " . $e->getMessage());
            return;
        }
        foreach ($queue as $job) {
            try {
                $this->bus->handle($job->command());
                $job->setComplete();
                $this->repo->persist($job);
            } catch (Throwable $e) {
                $this->handleException($job, $e);
            }
        }
    }

    protected function handleException(QueuedJob $job, Throwable $e)
    {
        $class = (new ReflectionClass($e))->getShortName();
        $this->logger->warning("Exception executing job ID:{$job->id()}: {$class} | {$e->getMessage()}");
        $job->decrementAttempts();
        $this->repo->persist($job);
        if ($job->failed()) {
            $class = get_class($job->command());
            $this->logger->error("Job ID:{$job->id()} ({$class}) has failed and will not be retried.");
        }
    }

    public function recent(int $days): Collection
    {
        return $this->repo->recent(intval($days));
    }

    public function allFailed(): Collection
    {
        return $this->repo->failed();
    }

    public function clearFailed(): void
    {
        $this->repo->clearFailed();
    }

    public function pause(int $minutes = 10): bool
    {
        return $this->lock->pause($minutes);
    }

    public function resume(): bool
    {
        return $this->lock->resume();
    }

    public function status(int $hours = 24): string
    {
        $outstanding = $this->repo->outstanding()->count();
        $failed = $this->repo->failed()->count();
        $completed = $this->repo->completed($hours);
        $completedCount = $completed->count();
        $totalTime = $completed->reduce(function ($total, QueuedJob $job) {
            return $total + $job->processingTime();
        }, 0);
        $avgTime = $completedCount ? number_format($totalTime / $completedCount, 1) : 0;
        return "QueueMgr: {$outstanding} outstanding, {$failed} failed, {$completedCount} completed, avg {$avgTime} seconds/job (last {$hours} hours)";
    }

    public function logStatus(int $hours = 24)
    {
        if ($this->allFailed()->count()) {
            $this->logger->warning($this->status($hours));
        } else {
            $this->logger->info($this->status($hours));
        }
    }
}
