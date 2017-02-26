<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
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
            // Destroy existing database
            Model::unguard();
            DB::raw(file_get_contents('database/scripts/resetDB.sql'));
            Model::reguard();

            // Re-creating with default values
            $this->call('migrate');
            $this->call('db:seed');
            $this->comment('Database correctly reseted.');
        }
        else {
            return $this->comment('Duh. I though I could destroy everything... (._.)');
        }
    }
}
