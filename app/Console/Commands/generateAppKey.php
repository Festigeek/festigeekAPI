<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class generateAppKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fg:generateKey
        {--f|force : Skip confirmation when overwriting an existing key.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the application key';

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
        $path = base_path('.env');

        if (file_exists($path)) {
            // check if there is already a secret set first
            if (!Str::contains(file_get_contents($path), 'APP_KEY=')) {
                $this->call('key:generate');
            } else {
                // let's be sure you want to do this, unless you already told us to force it
                $confirmed = $this->option('force') || $this->confirm('This will invalidate all existing app key. Are you sure you want to override it ?');
                if ($confirmed) {
                    $this->call('key:generate');
                } else {
                    return $this->comment('Phew... No changes were made to your app key.');
                }
            }
        }
    }
}
