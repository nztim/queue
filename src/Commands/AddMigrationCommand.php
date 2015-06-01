<?php namespace NZTim\Queue\Commands;

use Illuminate\Console\Command;
use Storage;

class AddMigrationCommand extends Command
{
    protected $name = 'queuemgr:migration';

    protected $description = 'Add database migration for Queue Manager';

    public function handle()
    {
        // Create a new migration
        $name = 'create_queuemgr_jobs_table';
        $ds = DIRECTORY_SEPARATOR;
        $path = $this->laravel->databasePath().$ds.'migrations';
        $filename = $this->laravel['migration.creator']->create($name, $path);
        // Overwrite with migration content
        $content = file_get_contents(__DIR__.$ds.'..'.$ds.'migration.stub');
        file_put_contents($filename, $content);
    }
}
