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
     * Get the participations for the Team.
     */
    public function participations()
    {
        return $this->hasMany('App\Participation');
    }

    /**
     * Get the EventProduct for the Team.
     */
    public function event_product()
    {
        return $this->belongsToMany('App\EventProduct', 'user_team', 'team_id', 'event_product_id');
    }

    /**
     * Get the products of the order.
     */
    public function users()
    {
        return $this->belongsToMany('App\User')->withPivot('captain')->withTimestamps();
    }
}
