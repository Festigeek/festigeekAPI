<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

use App\Mail\BankingWireTransfertMail;
use App\Order;
use App\User;

class sendBankTransfertMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fg:sendBankTransfertMail {order=null}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-send mail for bank transfert payment';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $order = Order::find($this->argument('order'));
        if(is_null($order))
            return $this->error('Order not found.');
        
        $user = $order->user()->first();
        Mail::to($user->email, $user->username)->send(new BankingWireTransfertMail($user, $order));
        
        return $this->comment('Mail sent to ' . $user->email);
    }
}
