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
    ];

    public function register()
    {
        App::bind('queuemgr', function() {
            $repo = App::make(QueuedJob\QueuedJobRepository::class);
            $mutexHandler = App::make(MutexHandler::class);
            return new QueueManager($repo, $mutexHandler);
        });
        $this->commands($this->commands);
    }

    public function boot()
    {
        //
    }
}
