<?php

namespace NZTim\Queue;

use Illuminate\Support\ServiceProvider;

class QueueServiceProvider extends ServiceProvider
{
    protected array $commands = [
        Commands\AddMigrationCommand::class,
        Commands\ClearFailedCommand::class,
        Commands\DaemonCommand::class,
        Commands\DeleteCommand::class,
        Commands\DumpCommand::class,
        Commands\FailedCommand::class,
        Commands\ListCommand::class,
        Commands\LogStatusCommand::class,
        Commands\PauseCommand::class,
        Commands\ProcessCommand::class,
        Commands\ResumeCommand::class,
        Commands\RetryCommand::class,
        Commands\StatusCommand::class,
    ];

    public function register()
    {
        $this->commands($this->commands);
    }
}
