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
            // check if state != (not cancelled)
              if($order->state != 3) {
                  Mail::to($order->user->email, $order->user->username)
                      ->send(new ConfirmationTicketMail($order->user, $order));
                  $this->comment('Mail sent => order: ' . $order->id . ', user: ' . $order->user->username);
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

        if(\Config::get('mail.driver') === 'smtp') {
            // Send email notification
            $transport = \Swift_SmtpTransport::newInstance(
                \Config::get('mail.host'),
                \Config::get('mail.port'),
                \Config::get('mail.encryption'))
                ->setUsername(\Config::get('mail.username'))
                ->setPassword(\Config::get('mail.password'))
                ->setStreamOptions(['ssl' => \Config::get('mail.ssloptions')]);

            $mailer = \Swift_Mailer::newInstance($transport);
            Mail::setSwiftMailer($mailer);
        }
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