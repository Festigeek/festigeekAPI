<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Lang;

class RegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
       * The user instance.
       *
       * @var User
       */
    protected $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.activate')
          ->subject(Lang::get('festigeek.notify_registration'))
          ->with([
            'registration_token' => $this->user->registration_token,
            'username' => $this->user->username
          ]);
    }
}
