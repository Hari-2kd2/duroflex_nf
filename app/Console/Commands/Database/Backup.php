<?php

namespace App\Console\Commands\Database;

use Carbon\Carbon;
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
        $filename = "backup-" . Carbon::now()->format('Y-m-dHmi') . ".sql";
        $path = "/usr/bin/mysqldump";
        $username = 'root'; //env('DB_USERNAME')
        $password = 'Pro@1234'; //env('DB_PASSWORD')
        $host = '127.0.0.1'; //env('DB_HOST');
        $database = 'duroflexflavours'; //env('DB_DATABASE')
        $command = $path . " --user=" . $username . " --password=" . $password . " --host=" . $host . " " . $database . "  > " . storage_path() . "/app/backup/" . $filename;

        echo $command;

        $returnVar = null;
        $output = null;

        exec($command, $output, $returnVar);

        return true;
    }
}
