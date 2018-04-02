<?php

namespace App\Mail;

use App\User;
use App\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BankingWireTransfertMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
       * The user instance.
       *
       * @var User
       */
    protected $user;
    protected $order;
    protected $total;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Order $order)
    {
        $this->user = $user;
        $this->order = $order;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $order_id = rand(10, 99) . $this->order->id . rand(10, 99);
        return $this->view('email.bankingWireTransfert')
            ->subject('Confirmation de ton inscription')
            ->with([
                'order_id'=> $order_id,
                'total' => $this->total,
                'order' => $this->order,
                'username' => $this->user->username
            ]);
    }
}
