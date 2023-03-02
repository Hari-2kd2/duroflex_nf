<?php

namespace App\Console\Commands\Database;

use Illuminate\Console\Command;

class Backup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $filename = strtolower(config('services.mysql.db_database')) . "-backup-" . date('Ymd') . ".sql";
        $path = ['server' => "/usr/bin/mysqldump", 'local' => "mysqldump"];

        $username = config('services.mysql.db_username'); //env('DB_USERNAME')
        $password = config('services.mysql.db_password'); //env('DB_PASSWORD')
        $host = config('services.mysql.db_host'); //env('DB_HOST');
        $database = config('services.mysql.db_database'); //env('DB_DATABASE')

        $command = $path[config('services.mysql.db_mysql_path')] . " --user=" . $username . " --password=" . $password . " --host=" . $host . " " . $database . "  > " . storage_path() . "/app/backup/" . $filename;
        
        $returnVar = null;
        $output = null;

        exec($command, $output, $returnVar);

        return true;
    }
}
