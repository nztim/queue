# Queue Manager

Command bus queue package for Laravel 6. Executes commands using nztim/commandbus.

### Installation

* `composer require nztim/queue`
* Add `NZTim\Queue\QueueServiceProvider::class,` to `config/app.php`
* Run `php artisan qm:migration && php artisan migrate` to add the queue table

### Usage

- `$qm->add($command)` will add a command to the queue.
- `php artisan qm:process` will process all jobs in the queue.
- Job failures will be logged as warnings, and final failures as errors.
- Queue processing is normally triggered via cron.
- `php artisan qm:daemon 50` processes the queue repeatedly for at least as long as the period specified (seconds).
- A lockfile is created in the storage folder to allow only only a single process to run.
  - It is recommended to not use `withoutOverlapping()` because if for any reason it's file mutex is not cleared then execution will halt indefinitely.
  - A warning will be logged if queue processing is skipped. This may indicate a lot of jobs or slow execution.
  - If something goes wrong and the lockfile is not cleared, it will time out after 20 minutes at which time normal processing will resume.
- Completed jobs are purged after 1 month.
- `php artisan qm:pause [10]` pauses the queue for the specified number of minutes or until manually resumed.
- `php artisan qm:resume` resumes paused queue processing if paused.
    - Typically surround your deployments with `pause` and `resume`
- `php artisan qm:logstatus` logs queue statistics for the last 24 hours

Example Task Scheduler:

```
$schedule->command('qm:daemon 50')->everyMinute();
$schedule->command('qm:logstatus')->dailyAt('4:00');
```

Other commands:
- `php artisan qm:status [24]` displays the queue status over the specified period (default 24 hours)
- `php artisan qm:list [7]` lists all jobs within the specified number of days
- `php artisan qm:failed` lists all failed jobs
- `php artisan qm:dump {id}` dumps contents of a particular job id
- `php artisan qm:retry {id}` retry failed job one more time
- `php artisan qm:clear` clears failed jobs from the queue

### Changelog
  * 9.0: Update to PHP 7.4 syntax
  * 8.0: Move to execution via command bus, remove use of Eloquent, major revision.
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
