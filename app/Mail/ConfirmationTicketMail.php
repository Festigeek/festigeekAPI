<?php

namespace App\Mail;

use App\User;
use App\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConfirmationTicketMail extends Mailable
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
      return $this->view('email.confirmationTicket')
        ->subject('Ton ticket d\'entrée FestiGeek 2017')
        ->with([
          'order' => $this->order,
          'user' => $this->user,
        ])->attachData($this->pdf, 'ticket.pdf', [
          'mime' => 'application/pdf',
        ]);
    }
}
