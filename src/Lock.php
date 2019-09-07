<?php namespace NZTim\Queue;

class Lock
{
    protected $lockfile;
    const STATUS_EXEC = 'executing';
    const STATUS_PAUSED = 'paused';

    public function __construct(string $lockfile = null)
    {
        if (is_null($lockfile)) {
            $lockfile = storage_path('app' . DIRECTORY_SEPARATOR . 'nztqueuemgr.lock');
        }
        $this->lockfile = $lockfile;
    }

    /**
     * Returns true if exclusive lock obtained
     * @param int $timeoutMinutes
     * @return bool
     */
    public function set(int $timeoutMinutes): bool
    {
        if ($this->isLocked()) {
            return false;
        }
        $this->setLock($timeoutMinutes);
        return true;
    }

    public function clear()
    {
        file_put_contents($this->lockfile, "0");
    }

    public function pause(int $timeoutMinutes): bool
    {
        if ($this->isLocked()) {
            return false;
        }
        $this->setLock($timeoutMinutes, true);
        return true;
    }

    public function resume(): bool
    {
        if (!$this->isPaused()) {
            return false;
        }
        $this->clear();
        return true;
    }

    protected function isPaused(): bool
    {
        return $this->status() == static::STATUS_PAUSED;
    }

    // Locked = executing or paused
    protected function isLocked(): bool
    {
        return $this->status() ? true : false;
    }

    protected function status()
    {
        if (!file_exists($this->lockfile)) {
            return false;
        }
        $lockdata = file_get_contents($this->lockfile);
        $exploded = explode('|', $lockdata);
        $locktime = intval($exploded[0]);
        $locktype = isset($exploded[1]) ? $exploded[1] : '';
        return $locktime > time() ? $locktype : false;
    }

    protected function setLock(int $timeoutMinutes, $paused = false)
    {
        $data = strval(time() + ($timeoutMinutes * 60)) . '|';
        $data .= $paused ? static::STATUS_PAUSED : static::STATUS_EXEC;
        file_put_contents($this->lockfile, $data);
    }
}
