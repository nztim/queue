#Queue Manager

Simple job queue package for Laravel 5

Install via `config/app.php`:  
Add to $providers: `NZTim\Queue\QueueServiceProvider::class,`  
Add to $aliases: `'QueueMgr' => NZTim\Queue\QueueMgrFacade::class,`  

`php artisan queuemgr:migration` to add migration file
`php artisan migrate` to run it and add the `queuemgrjobs` table

Optional `.env` setting:  
- `QUEUEMGR_MAX_AGE` the maximum age (in hours) before `queuemgr:check` reports an error, default is 1 hour
- `QUEUEMGR_EMAIL` set this to your email address to receive an message when `queuemgr:check` reports an error
- `QUEUEMGR_ATTEMPTS` sets the default number of attempts for a job, default is 5 times

###Usage

- Jobs must implement `NZTim\Queue\Job` interface, which consists solely of a `handle()` method.
- `QueueMgr::add(new Job)` adds a `Job` to the queue
- `php artisan queuemgr:process` runs all the jobs in the queue.  Job failures will be logged as warnings, and final failures as errors.  
- `php artisan queuemgr:check` if this command finds jobs that exceed the specified age, it logs an error and optionally sends an email.
- Completed jobs are soft-deleted initially and purged after 1 month.

Typical Task Scheduler:
<code php>
$schedule->command('queuemgr:process')->everyMinute()->withoutOverlapping();
$schedule->command('queuemgr:check')->hourly();
</code>

Alternatively, set your cron to run `queuemgr:process` at your preferred interval.

Other commands:
- `php artisan queuemgr:list [7]` lists all jobs within the specified number of days
- `php artisan queuemgr:failed` lists all failed jobs
- `php artisan queuemgr:clear` clears failed jobs from the queue