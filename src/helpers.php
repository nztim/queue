<?php

use NZTim\Queue\Job;
use NZTim\Queue\QueueManager;

if (!function_exists('qa')) {
    function qa(Job $job): void
    {
        app(QueueManager::class)->add($job);
    }
}
