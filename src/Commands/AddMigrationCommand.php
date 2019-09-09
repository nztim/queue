<?php

namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\MigrationCreator;

class AddMigrationCommand extends Command
{
    protected $signature = 'qm:migration';

    protected $description = 'Add database migration for Queue Manager';

    public function handle()
    {
        /** @var MigrationCreator $migrationCreator */
        $migrationCreator = app('migration.creator');
        $filename = $migrationCreator->create('create_update_queuemgr_jobs_table', database_path('migrations'));
        // Overwrite with migration content
        $stub = __DIR__. '/../../migrations/add_update_table.stub';
        file_put_contents($filename, file_get_contents($stub));
    }
}
