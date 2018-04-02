<?php

namespace App\Mail;

use App\User;
use App\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeamOwnerMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user instance.
     *
     * @var User
     */
    protected $user;
    protected $team;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Team $team)
    {
        $this->user = $user;
        $this->team = $team;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $name = $this->team->name;
        return $this->view('email.teamOwner')
            ->subject("Informations pour l'équipe $name")
            ->with([
                'team' => $this->team,
                'username' => $this->user->username
            ]);
    }
}
