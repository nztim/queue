<?php namespace NZTim\Queue;

use Log;
use Mail;

class MutexHandler
{
    protected $mutex;

    public function __construct()
    {
        $this->mutex = storage_path() . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'queuemgr.mutex';
    }

    /**
     * @return boolean
     */
    public function startRun()
    {
        // If no file then we are good to go, begin with a count of 0 blocked runs
        if (!file_exists($this->mutex)) {
            return $this->newMutex();
        }
        // File exists so QueueMgr is already running
        $fileContent = file_get_contents($this->mutex);
        if ($fileContent === false || !$this->isSerialized($fileContent)) {
            Log::warning('QueueMgr removed invalid mutex file');
            unlink($this->mutex);
            return $this->newMutex();
        }
        $count = intval(unserialize($fileContent));
        $max = env('QUEUEMGR_MAX_BLOCKED', 15);
        if ($count > intval($max)) {
            $this->maxBlocks();
            return $this->newMutex();
        }
        // Increment blocked counter and stop the run
        $count++;
        file_put_contents($this->mutex, serialize($count));
        return false;
    }

    protected function newMutex()
    {
        file_put_contents($this->mutex, serialize(0));
        return true;
    }

    protected function maxBlocks()
    {
        Log::error('QueueMgr reached maximum blocked attempts, cleared mutex and allowed processing');
        $email = env('QUEUEMGR_EMAIL', null);
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $url = url('/');
            $message = "The QueueMgr jobs queue on {$url} has reached maximum blocked attempts, cleared mutex and allowed processing.";
            Mail::raw($message, function ($message) use ($email, $url) {
                $message->to($email)->subject('QueueMgr job queue failure on ' . $url);
            });
        }
        unlink($this->mutex);
    }

    public function endRun()
    {
        if (!file_exists($this->mutex)) {
            Log::warning('QueueMgr reached end of run and mutex file does not exist');
            return;
        }
        unlink($this->mutex);
    }

    // From WordPress
    protected function isSerialized($data)
    {
        // if it isn't a string, it isn't serialized
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (!preg_match('/^([adObis]):/', $data, $badions)) {
            return false;
        }
        switch ($badions[1]) {
            case 'a':
            case 'O':
            case 's':
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
                    return true;
                }
                break;
            case 'b':
            case 'i':
            case 'd':
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)) {
                    return true;
                }
                break;
        }
        return false;
    }
}