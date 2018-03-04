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
//        $users = $this->users()->get(['username', 'gender'])->makeHidden(['QRCode', 'pivot']);

//        $orders = $this->orders()->get()->filter(function($order){
//            return $order->state !== 3;
//        });
        //if dans l'equipe


        $orders = $this->orders()->where('state', '<>', 3)->get();
        $isAMember = $orders->contains('user_id', 1); //TODO get the real user !!!Very Important!!!! 
        $users = $orders->map(function($order) use ($isAMember) {
            $roaster = $order->products()->where('product_type_id', 1)->first()->id == $this->defaultProduct()->id;
            $user = [
              'id' => $order->user->id,
              'username' => $order->user->username,
              'gender' => $order->user->gender,
              'email' => $order->user->when($isAMember, function() use($order){
                return $order->user->email;
              }),
              'roaster' => $roaster
            ];
            return $user;
        });

        return $users;
    }

    // public function getDetailedUsersAttribute(){
    //   $orders = $this->orders()->where('state', '<>', 3)->get();
    //
    //   $users = $orders->map(function($order) {
    //       $roaster = $order->products()->where('product_type_id', 1)->first()->id == $this->defaultProduct()->id;
    //       $fullname = $order->user->firstname ." ". $order->user->lastname;
    //       return ['id' => $order->user->id,'username' => $order->user->username, 'email' => $order->user->email, 'name' => $fullname, 'gender' => $order->user->gender, 'roaster' => $roaster];
    //   });
    //   return $users;
    // }
}
