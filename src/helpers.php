<?php

use NZTim\Queue\QueueManager;

if (!function_exists('qa')) {
    function qa(...$args): void
    {
        app(QueueManager::class)->add(...$args);
    }
}
