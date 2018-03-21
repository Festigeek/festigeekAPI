<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class resetDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fg:resetDB
        {--f|force : Skip confirmation when resetting the database.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop all tables then re-migrate and re-seed.';

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
     * @return mixed
     */
    public function handle()
    {
        $confirmed = $this->option('force') || $this->confirm('This will DESTROY your database then recreate it with default data. Are you sure you want to do it ?');
        if ($confirmed) {
            // Destroy tables in existing database
            $droplist = [];
            $colname = 'Tables_in_' . env('DB_DATABASE');

            $tables = DB::select('SHOW TABLES');
            foreach($tables as $table) {
                $droplist[] = $table->$colname;
            }

            if(!empty($droplist)) {
                $droplist = implode(', ', $droplist);
                $sql = 'DROP TABLE ' . $droplist . ';';

                DB::beginTransaction();
                //turn off referential integrity
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');
                DB::statement($sql);
                //turn referential integrity back on
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
                DB::commit();
            }

            // Re-creating with default values
            $this->call('cache:clear');
            $this->call('migrate');

            $this->comment(PHP_EOL . 'Seeding database...');
            $this->call('db:seed');
            $this->call('passport:install');

            $this->comment('Database correctly reseted. Do not forget to update your OAUTH_PASSWORD_CLIENT_SECRET in your .env file.');
        }
        else {
            return $this->comment(PHP_EOL . 'Duh. I though I could destroy everything... (._.)');
        }
    }
}
