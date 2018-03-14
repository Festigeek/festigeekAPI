<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'pivot'
    ];

    /**
     * The attributes added to the model.
     *
     * @var array
     */
    protected $appends = [
        'users'
    ];

    /**
     * Get the EventProduct for the Team.
     */
    public function orders()
    {
        return $this->belongsToMany('App\Order', 'team_user')
            ->withPivot('captain')
            ->withTimestamps();
    }

    /**
     * Get the users of the team.
     */
    public function users()
    {
        return $this->belongsToMany('App\User')->withPivot('captain')->withTimestamps();
    }

    /**
     * Get the default product of the team (first product of type "tournament", in the first order).
     */
    public function defaultProduct()
    {
        return $this->orders()->first()->products->where('product_type_id', 1)->first();
    }

    /**
     * Return a simplified users array for each teams
     *
     * @return String
     */
    public function getUsersAttribute() {
        // TODO write different value if user is in the team (or admin) maybe create user->isInTeam($team_id) ?
//        $users = $this->users()->get(['username', 'gender'])->makeHidden(['QRCode', 'pivot']);

        // Get correct users for a specific team
        $orders = $this->orders()->where('state', '<>', 3)->get();
        $users = $orders->map(function($order) {
            $roaster = $order->products()->where('product_type_id', 1)->first()->id == $this->defaultProduct()->id;
            $user = [
                'username' => $order->user->username,
                'gender' => $order->user->gender,
                'roaster' => $roaster
            ];

            // Add some informations for members of the team
            if (auth()->user() && $this->hasUser(auth()->user()->id)) {
                $user['firstname'] = $order->user->firstname;
                $user['lastname'] = $order->user->lastname;
                $user['email'] = $order->user->email;
            }

            return $user;
        });

        return $users;
    }

    /**
     * Check if a given user is a member of this team
     *
     * @return bool
     */
    private function hasUser($user_id) {
        return $this->users()->get()->contains('id', $user_id);
    }
}
