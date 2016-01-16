<?php namespace NZTim\Queue;

use App;
use Illuminate\Cache\Repository;
use Illuminate\Mail\Mailer;
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
        Commands\QueuemgrListCommand::class,
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
