#Queue Manager

Simple job queue package for Laravel 5

Install via `config/app.php`:  
Add to $providers: `NZTim\Queue\QueueServiceProvider::class,`  
Add to $aliases: `'QueueMgr' => NZTim\Queue\QueueMgrFacade::class,`  

`php artisan queuemgr:migration` to add migration file
`php artisan migrate` to run it and add the `queuemgrjobs` table

Optional `.env` setting:  
- `QUEUEMGR_ATTEMPTS` sets the default number of attempts for a job, default is 5 times

###Usage

- Jobs must implement `NZTim\Queue\Job` interface, which consists solely of a `handle()` method.
- `QueueMgr::add(new Job)` adds a `Job` to the queue
- `php artisan queuemgr:process` runs all the jobs in the queue.  Job failures will be logged as warnings, and final failures as errors.
- `php artisan queuemgr:daemon` processes the queue repeatedly for at least as long as the period specified (seconds). 
- Queue processing is normally triggered via cron. 
- A mutex is stored in the cache to allow only a single process to run.
  - It is recommended to not use `withoutOverlapping()` because if for any reason it's file mutex is not cleared then execution will halt indefinitely.
  - A warning will be logged if queue processing is skipped. This may indicate a lot of jobs or slow execution.
  - If something goes wrong and the mutex is not cleared, it will time out after 30 minutes at which time normal processing will resume.
- Completed jobs are soft-deleted initially and purged after 1 month.
- `php artisan queuemgr:pause 10` pauses the queue for the specified number of minutes or until the cache is cleared. Useful for deployments.
- `php artisan queuemgr:logstatus` logs queue statistics for the last 24 hours

Example Task Scheduler:

```
$schedule->command('queuemgr:daemon 50')->everyMinute();
$schedule->command('queuemgr:logstatus')->dailyAt('4:00');
```

Other commands:
- `php artisan queuemgr:list [7]` lists all jobs within the specified number of days
- `php artisan queuemgr:failed` lists all failed jobs
- `php artisan queuemgr:clear` clears failed jobs from the queue

### Changelog
  * v5: Add `daemon()` method for faster processing. Add `pause()` for reliable deployments.
    * To upgrade: replace use `daemon` via cron instead of `process`, add daily `logstatus` cron, update deployment process to use `pause 10` followed by cache clear on completion
  * v4:
    * `QueueMgr::check()` removed as is use of `withoutOverlapping()`
    * `QUEUEMGR_EMAIL` and `QUEUEMGR_MAX_AGE` options removed
    * To upgrade, just remove the unnecessary calls and .env options. Use your error handler (e.g. Logger) for email notifications of failures.
