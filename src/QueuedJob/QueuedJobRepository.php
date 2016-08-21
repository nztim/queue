<?php namespace NZTim\Queue\QueuedJob;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use NZTim\Queue\Job;

class QueuedJobRepository
{
    protected $model;

    public function __construct(QueuedJob $model)
    {
        $this->model = $model;
    }

    public function newInstance(Job $job, int $attempts) : QueuedJob
    {
        $queuedJob = $this->model->newInstance();
        $queuedJob->job = serialize($job);
        $queuedJob->attempts = (int) $attempts;
        return $queuedJob;
    }

    public function persist(QueuedJob $job)
    {
        $job->save();
    }

    public function recent(int $days) : Collection
    {
        return $this->model->withTrashed()
            ->where('created_at', '>', Carbon::now()->subDays($days))
            ->orderBy('created_at', 'asc')->get();
    }

    public function allOutstanding() : Collection
    {
        return $this->model->outstanding()->get();
    }

    public function allFailed() : Collection
    {
        return $this->model->allFailed()->get();
    }

    public function purgeDeleted()
    {
        $this->model->deletedAndOld()->forceDelete();
    }

    public function delete(QueuedJob $job)
    {
        $job->delete();
    }

    public function clearFailed()
    {
        $this->model->allFailed()->delete();
    }
}
