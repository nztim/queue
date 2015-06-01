<?php namespace NZTim\Queue;

use App;
use Illuminate\Support\ServiceProvider;

class QueueServiceProvider extends ServiceProvider
{
    protected $defer = false;

    protected $commands = [
        'NZTim\Queue\Commands\AddMigrationCommand',
        'NZTim\Queue\Commands\QueuemgrProcessCommand',
        'NZTim\Queue\Commands\QueuemgrFailedCommand',
        'NZTim\Queue\Commands\QueuemgrClearFailedCommand',
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
