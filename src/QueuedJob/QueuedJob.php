<?php namespace NZTim\Queue\QueuedJob;

use BadMethodCallException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use NZTim\Queue\Job;

class QueuedJob extends Model
{
    // Eloquent ===============================================================
    protected $table = 'queuemgrjobs';
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    /*
     * Completed jobs are soft-deleted and purged after 1 month
     * Outstanding jobs are not deleted
     * Jobs with 0 attempts remaining will not be retried
     */

    public function scopeOutstanding(Builder $query)
    {
        return $query->where('attempts', '>', 0);
    }

    public function scopeDeletedAndOld(Builder $query)
    {
        return $query->onlyTrashed()->where('deleted_at', '<', Carbon::now()->subMonth());
    }

    public function scopeAllFailed(Builder $query)
    {
        return $query->where('attempts', 0);
    }

    // Entity =================================================================

    public function getId(): int
    {
        return $this->id;
    }

    public function getJob()
    {
        return unserialize($this->job);
    }

    public function decrementAttempts()
    {
        $this->attempts--;
        if ($this->attempts < 0) {
            throw new BadMethodCallException('Cannot decrement attempts lower than 0');
        }
    }

    public function failed(): bool
    {
        return $this->attempts == 0 ? true : false;
    }

    public function processingTime(): int
    {
        if (is_null($this->deleted_at)) {
            return 0;
        }
        return $this->deleted_at->diffInSeconds($this->created_at);
    }
}
