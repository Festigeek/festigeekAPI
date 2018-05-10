<?php

namespace App\Console\Commands;

use App\Event;
use App\Mail\ConfirmationTicketMail;
use App\Order;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

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
            if ($this->confirm('Are you sure you want to send ' . $orderList->count() . ' e-mail(s) ?')) {
                $orderList->each(function($order) {
                    try {
                        $this->sendTicketMails($order);
                    }
                    catch(Exception $e){
                        $this->error('Error when sending mail for order #' . $order->id);
                    }
                });
            }
            else
                return $this->comment('Ok, cancelling mails sending...');
        }
        else {
            Mail::to($order->user->email, $order->user->username) ->send(new ConfirmationTicketMail($order->user, $order));
            $this->comment('Mail sent => order: ' . $order->id . ', user: ' . $order->user->username);
            return;
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $this->event = Event::find($this->argument('event'));
        // if(is_null($this->event))
        //     return $this->error('Event not found.');
        //
        // if(!$this->option('all')) {
        //     $order = Order::find($this->argument('order'));
        //     if(is_null($order))
        //         return $this->error('Order not found.');
        // }
        //
        // $this->sendTicketMails($order);
        // return $this->comment('Command successful.');
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
        $this->sendTicketMails($order);
        try {
        //            $this->sendTicketMails($order);
        }
        catch(Exception $e) {
            return $this->error('Error when sending mail for order #' . $order->id);
        }
        return $this->comment('Command successful.');
        }
    }
}
