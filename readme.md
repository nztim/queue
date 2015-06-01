#Queue

Simple job queue package for Laravel 5

Configure via `config/app.php`:  
Add to $providers: `'NZTim\Queue\QueueServiceProvider'`  
Add to $aliases: `'QueueMgr' => 'NZTim\Queue\QueueMgrFacade',`  

`php artisan queuemgr:migration` to add migration file
`php artisan migrate` to run it

Optional `.env` settings:  
`QUEUEMGR_ATTEMPTS` sets the default number of attempts for a job

###Jobs

Job classes must implement `NZTim\Queue\Job` interface, which consists solely of a `handle()` method.

###Usage

`php artisan queuemgr:process` runs all the jobs in the queue.  Job failures will be logged as warnings, and final failures as errors.  
`php artisan queuemgr:failed` lists all failed jobs.  
`php artisan queuemgr:clear` clears failed jobs from the queue.  

All jobs are soft-deleted initially and purged after 1 month.

The Laravel scheduler is recommended because it offers the ability to run the command often but without overlapping:    
`$schedule->command('queuemgr:process')->withoutOverlapping()->everyFiveMinutes();`  or  
`$schedule->command('queuemgr:process')->withoutOverlapping()->cron('*/2 * * * *');;`   

Alternatively, set your cron to run `queuemgr:process` at your preferred interval.   
If you process the queue frequently and the processes are slow, it's possible to duplicate a job, so the Laravel scheduler is recommended. 