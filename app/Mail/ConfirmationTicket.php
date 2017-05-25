<?php

namespace App\Mail;

use App\User;
use App\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConfirmationTicket extends Mailable
{
    use Queueable, SerializesModels;

    /**
       * The user instance.
       *
       * @var User
       */
    protected $user;
    protected $order;
    protected $pdf;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Order $order)
    {
        $this->user = $user;
        $this->order = $order;
        $html =  view('pdf.ticket', ['order' => $order, 'user' => $user]);
        $this->pdf = \PDF::loadHTML($html)->setPaper('a4')->setOption('margin-bottom', 0)->inline('ticket_lan.pdf');
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      return $this->view('email.bankingWireTransfert')
        ->subject('Confirmation de ton inscription')
        ->with([  
          'order_id'=>$this->order->id,
          'order' => $this->order,
          'username' => $this->user->username
        ])->attachData($this->pdf, 'ticket.pdf', [
          'mime' => 'application/pdf',
        ]);
    }
}
