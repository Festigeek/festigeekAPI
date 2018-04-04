<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

class ResetPasswordTokenMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
       * The user instance.
       *
       * @var User
       */
    protected $user;
    protected $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.resetPassword')
          ->subject(Lang::get('festigeek.reset_token'))
          ->with([
            'reset_token' => $this->token,
            'username' => $this->user->username
          ]);
    }
}
