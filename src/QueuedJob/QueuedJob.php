<?php namespace NZTim\Queue\QueuedJob;

use BadMethodCallException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use NZTim\Queue\Job;
use Illuminate\Database\Eloquent\SoftDeletes;

class QueuedJob extends Model
{
    // Eloquent ===============================================================
    protected $table = 'queuemgrjobs';
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function scopeOutstanding($query)
    {
        return $query->where('attempts', '>', 0);
    }

    public function scopeDeletedAndOld($query)
    {
        return $query->onlyTrashed()->where('deleted_at', '<', Carbon::now()->subMonth());
    }

    public function scopeAllFailed($query)
    {
        return $query->where('attempts', '=', 0);
    }

    // Entity =================================================================
    /**
     * @param Job $job
     * @param $attempts
     * @return QueuedJob
     */
    public static function newJob(Job $job, $attempts)
    {
        $queuedJob = new static;
        $queuedJob->job = serialize($job);
        $queuedJob->attempts = (int) $attempts;
        return $queuedJob;
    }

    public function getId()
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
        if($this->attempts < 0 ) {
            throw new BadMethodCallException('Cannot decrement attempts lower than 0');
        }
    }

    public function failed()
    {
        return $this->attempts == 0 ? true : false;
    }
}
