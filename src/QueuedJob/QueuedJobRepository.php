<?php namespace NZTim\Queue\QueuedJob;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use NZTim\Queue\Job;

class QueuedJobRepository
{
    public function newInstance(Job $job, int $attempts): QueuedJob
    {
        $queuedJob = (new QueuedJob())->newInstance();
        $queuedJob->job = serialize($job);
        $queuedJob->attempts = (int) $attempts;
        return $queuedJob;
    }

    public function persist(QueuedJob $job): void
    {
        $job->save();
    }

    public function recent(int $days): Collection
    {
        return (new QueuedJob())->withTrashed()
            ->where('created_at', '>', Carbon::now()->subDays($days))
            ->orderBy('created_at', 'asc')->get();
    }

    public function completed(int $hours): Collection
    {
        return (new QueuedJob())->onlyTrashed()->where('created_at', '>', Carbon::now()->subHours($hours))->get();
    }

    public function allOutstanding(): Collection
    {
        return (new QueuedJob())->outstanding()->get();
    }

    public function allFailed(): Collection
    {
        return (new QueuedJob())->allFailed()->get();
    }

    public function purgeDeleted(): void
    {
        (new QueuedJob())->deletedAndOld()->forceDelete();
    }

    public function delete(QueuedJob $job): void
    {
        $job->delete();
    }

    public function clearFailed(): void
    {
        (new QueuedJob())->allFailed()->delete();
    }
}
