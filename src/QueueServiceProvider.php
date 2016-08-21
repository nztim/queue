<?php namespace NZTim\Queue;

use App;
use Illuminate\Support\ServiceProvider;

class QueueServiceProvider extends ServiceProvider
{
    protected $defer = false;

    protected $commands = [
        Commands\AddMigrationCommand::class,
        Commands\ProcessCommand::class,
        Commands\FailedCommand::class,
        Commands\ClearFailedCommand::class,
        Commands\ListCommand::class,
    ];

    public function register()
    {
        App::bind('queuemgr', function() {
            return App::make(QueueManager::class);
        });
        $this->commands($this->commands);
    }

    public function boot()
    {
        //
    }
}
