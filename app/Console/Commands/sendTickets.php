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
    private function sendTicketMails($order)
    {
        if(is_null($order)) {
            $orderList = Order::where('event_id', $this->event->id)->get();

            // let's be sure you want to do this
            $confirmed = $this->confirm('Are you sure you want to send ' . $orderList->count() . ' e-mail(s) ?');
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

        if ($order instanceof Model) {

            // check if state = 1 (is paid)

              if($order->state == 1){
                //TODO send mail here
                $this->comment('mail sent, '. $order->id . ' user: ' . $order->user->username);
              }

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
            $this->event = Event::where('id', (int)$this->argument('event'))->firstOrFail();

        }
        catch(Exception $e) {
            return $this->error('Event not found.');
        }

        try {

            $order = (($this->argument('order')==='null') && $this->option('all')) ? null : Order::findOrFail($this->argument('order'));
            //return $this->comment($order);
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
