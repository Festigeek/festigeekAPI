<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Order;
use App\Event;

class generateLanPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'fg:generateLanPassword {order=null}
    {--renew : Re-generate all LAN keys.}
    {--f|force : Skip confirmation when overwriting an existing key.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate lan password';

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
        $chars = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
            '2', '3', '4', '5', '6', '7', '8', '9');

        try {
            $this->event = Event::where('id', (int)$this->argument('event'))->firstOrFail();
        }
        catch(Exception $e) {
            return $this->error('Event not found.');
        }

        $confirmed = $this->option('force') || $this->confirm('This will regenerate all existing intranet password. Are you sure you want to override it ?');
        if ($confirmed) {
            $orders = Order::where('event_id', $this->event->id)->get();
            $passwords = array();

            foreach ($orders as $order) {
                $alreadyUsed = false;
                do {
                    $password = "";
                    for ($i = 0; $i < 8; $i++) {
                        $password .= $chars[rand(0, sizeof($chars) - 1)];
                    }
                    $alreadyUsed = array_key_exists($password, $passwords);
                    array_push($passwords, $password);
                    $order->code_lan = (!is_null($order->code_lan)) ? $order->code_lan : $password;
                    $order->save();
                } while ($alreadyUsed);
            }
        }
        else {
            return $this->comment('No password generated.');
        }
    }
}
