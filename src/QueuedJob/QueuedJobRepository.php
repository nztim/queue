<?php namespace NZTim\Queue\QueuedJob;

use Illuminate\Contracts\Queue\Queue;
use Illuminate\Database\Eloquent\Model;

class QueuedJobRepository
{
    protected $model;

    public function __construct(QueuedJob $model)
    {
        $this->model = $model;
    }

    public function persist(QueuedJob $job)
    {
        /** @var Model $job */
        $job->save();
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