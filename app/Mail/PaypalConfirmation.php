<?php

namespace App\Mail;

use App\User;
use App\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaypalConfirmation extends Mailable
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
  public function __construct(User $user, Order $order, $total)
  {
    $this->user = $user;
    $this->order = $order;
    $this->total = $total;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->view('email.paypalConfirmation')
      ->subject("Confirmation de paiement")
      ->with([
        'total' => $this->total,
        'order' => $this->order,
        'username' => $this->user->username
      ]);
  }
}
