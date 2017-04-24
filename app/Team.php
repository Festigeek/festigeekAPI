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
     * Get the EventProduct for the Team.
     */
    public function orders()
    {
        return $this->belongsToMany('App\Order', 'team_user')
            ->withPivot('captain')
            ->withTimestamps();
    }

    /**
     * Get the products of the order.
     */
    public function users()
    {
        return $this->belongsToMany('App\User')->withPivot('captain')->withTimestamps();
    }
}
