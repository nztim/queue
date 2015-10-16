#Queue

Simple job queue package for Laravel 5

Configure via `config/app.php`:  
Add to $providers: `NZTim\Queue\QueueServiceProvider::class`  
Add to $aliases: `'QueueMgr' => NZTim\Queue\QueueMgrFacade::class,`  

`php artisan queuemgr:migration` to add migration file
`php artisan migrate` to run it

Optional `.env` setting:  
 - `QUEUEMGR_ATTEMPTS` sets the default number of attempts for a job
 
###Jobs

Job classes must implement `NZTim\Queue\Job` interface, which consists solely of a `handle()` method.

###Usage

`php artisan queuemgr:process` runs all the jobs in the queue.  Job failures will be logged as warnings, and final failures as errors.  
`php artisan queuemgr:failed` lists all failed jobs.  
`php artisan queuemgr:clear` clears failed jobs from the queue.  

All jobs are soft-deleted initially and purged after 1 month.

The Laravel scheduler can be used however do not use `withoutOverlapping()` as it is currently too fragile.    
`$schedule->command('queuemgr:process')->everyMinute();`  or  
`$schedule->command('queuemgr:process')->cron('*/2 * * * *');;`   

Alternatively, set your cron to run `queuemgr:process` at your preferred interval.   

### Preventing overlaps

QueueMgr takes responsibility for preventing overlaps so you may run the command as often as you wish.

A mutex file is used for preventing overlaps, and it contains a counter.
If there is an error in processing the queue, or a server error, it's possible the mutex file will not be cleared.
If processing is prevented 15 times by the same mutex file, then an error is logged, the counter is cleared and processing is allowed to continue.

Optional settings:  
You can override the number of times by setting the `.env` value `QUEUEMGR_MAX_BLOCKED` to a value of your choice.
You can opt to receive an email in the event by setting the `.env` value `QUEUEMGR_EMAIL` to your preferred email address.
