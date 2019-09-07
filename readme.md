# Queue Manager

Simple job queue package for Laravel 5

Install via `config/app.php`:
Register provider: `NZTim\Queue\QueueServiceProvider::class,`

`php artisan queuemgr:migration` to add migration file
`php artisan migrate` to run it and add the `queuemgrjobs` table

### Usage

- Jobs must implement `NZTim\Queue\Job` interface, which consists solely of a `handle()` method.
- `$qm->add(new Job)` adds a `Job` to the queue, or use helper function: `qa(new Job)`
- `php artisan queuemgr:process` runs all the jobs in the queue.
    - Job failures will be logged as warnings, and final failures as errors.
- Queue processing is normally triggered via cron.
- `php artisan queuemgr:daemon 50` processes the queue repeatedly for at least as long as the period specified (seconds).
- A lockfile is created in the storage folder to allow only only a single process to run.
  - It is recommended to not use `withoutOverlapping()` because if for any reason it's file mutex is not cleared then execution will halt indefinitely.
  - A warning will be logged if queue processing is skipped. This may indicate a lot of jobs or slow execution.
  - If something goes wrong and the lockfile is not cleared, it will time out after 20 minutes at which time normal processing will resume.
- Completed jobs are soft-deleted initially and purged after 1 month.
- `php artisan queuemgr:pause 10` pauses the queue for the specified number of minutes or until manually resumed.
- `php artisan queuemgr:resume` resumes paused queue processing if paused.
    - Typically surround your deployments with `pause` and `resume`
- `php artisan queuemgr:logstatus` logs queue statistics for the last 24 hours

Example Task Scheduler:

```
$schedule->command('queuemgr:daemon 50')->everyMinute();
$schedule->command('queuemgr:logstatus')->dailyAt('4:00');
```

Other commands:
- `php artisan queuemgr:status [24]` displays the queue status over the specified period (default 24 hours)
- `php artisan queuemgr:list [7]` lists all jobs within the specified number of days
- `php artisan queuemgr:failed` lists all failed jobs
- `php artisan queuemgr:dump {id}` dumps contents of a particular job id
- `php artisan queuemgr:retry {id}` retry failed job one more time
- `php artisan queuemgr:clear` clears failed jobs from the queue

### Changelog
  * 7.0: Remove facade and .env entries.
  * 6.4: Add retry command
  * 6: Replace cache lock with lockfile. Add `resume()` method.
    * To upgrade: add `resume` to deployment scripts after deployment is complete.
  * 5: Add `daemon()` method for faster processing. Add `pause()` for reliable deployments.
    * To upgrade: replace use `daemon` via cron instead of `process`, add daily `logstatus` cron, update deployment process to use `pause 10` followed by cache clear on completion
  * 4:
    * `QueueMgr::check()` removed as is use of `withoutOverlapping()`
    * `QUEUEMGR_EMAIL` and `QUEUEMGR_MAX_AGE` options removed
    * To upgrade, just remove the unnecessary calls and .env options. Use your error handler (e.g. Logger) for email notifications of failures.
