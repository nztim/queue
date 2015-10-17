<?php namespace NZTim\Queue\QueuedJob;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use NZTim\Queue\Job;

class QueuedJobRepository
{
    protected $model;

    public function __construct(QueuedJob $model)
    {
        $this->model = $model;
    }

    /**
     * @param Job $job
     * @param Integer $attempts
     * @return QueuedJob
     */
    public function newInstance(Job $job, $attempts)
    {
        $queuedJob = $this->model->newInstance();
        $queuedJob->job = serialize($job);
        $queuedJob->attempts = (int) $attempts;
        return $queuedJob;
    }

    /**
     * @param QueuedJob $job
     */
    public function persist(QueuedJob $job)
    {
        /** @var Model $job */
        $job->save();
    }

    /**
     * @param Integer $days
     * @return Collection
     */
    public function recent($days)
    {
        return $this->model->withTrashed()
            ->where('created_at', '>', Carbon::now()->subDays($days))
            ->orderBy('created_at', 'asc')->get();
    }

    public function allOutstanding()
    {
        return $this->model->outstanding()->get();
    }

    public function allFailed()
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