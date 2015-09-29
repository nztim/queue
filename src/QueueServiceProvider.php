<?php namespace NZTim\Queue;

use App;
use Illuminate\Support\ServiceProvider;

class QueueServiceProvider extends ServiceProvider
{
    protected $defer = false;

    protected $commands = [
        Commands\AddMigrationCommand::class,
        Commands\QueuemgrProcessCommand::class,
        Commands\QueuemgrFailedCommand::class,
        Commands\QueuemgrClearFailedCommand::class,
        Commands\QueuemgrCheckCommand::class,
    ];
    public function register()
    {
        App::bind('queuemgr', function() {
            $repo = App::make('NZTim\Queue\QueuedJob\QueuedJobRepository');
            return new QueueManager($repo);
        });
        $this->commands($this->commands);
    }

    public function boot()
    {
        //
    }
}
