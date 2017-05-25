<?php

namespace App\Console\Commands;

use App\Event;
use App\Order;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class sendTickets extends Command
{
    private $event;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fg:sendTickets {event=null} {order=null}
        {--a|all : Send tickets to every event\'s orders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send mail(s) with MIP tickets';

    /**
     * Send mail(s).
     *
     * @return void
     */
    private function sendTicketMails($orders)
    {
        if(is_null($orders)) {
            $orderList = Order::where('event_id', $this->event->id)->get();

            // let's be sure you want to do this
            $confirmed = $this->confirm('Are you sure you want to send ' . $orderList->count() . ' e-mail ?');
            if ($confirmed) {
                $orderList->each(function($order){
                    try {
                        $this->sendTicketMails($order);
                    }
                    catch(Exception $e){
                        $this->error('Error when sending mail for order #' . $order->id);
                    }
                });
            } else {
                return $this->comment('Ok, cancelling mails sending...');
            }

        }

        if ($orders instanceof Model) {
            // TODO Send mail
            return;
        }
    }

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
        try {
            $this->event = Event::firstOrFail($this->argument('event'));
        }
        catch(Exception $e) {
            return $this->error('Event not found.');
        }

        try {
            $order = (is_null($this->argument('order')) && $this->option('all')) ? null : Order::firstOrFail($this->argument('order'));
        }
        catch(Exception $e) {
            return $this->error('All flag or Order not found.');
        }

        try {
            $this->sendTicketMails($order);
        }
        catch(Exception $e) {
            return $this->error('Error when sending mail for order #' . $order->id);
        }

        return $this->comment('Command successful.');
    }
}
