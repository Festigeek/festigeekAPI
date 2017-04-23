<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'begins_at', 'ends_at', 'description'
    ];

    /**
     * Get the products for the specific event.
     */
    public function products()
    {
        return $this->belongsToMany('App\Product')->withPivot('quantity_max', 'sold')->withTimestamps();
    }

    /**
     * Get all the participations for the event.
     */
    public function participations()
    {
        return $this->hasManyThrough('App\Participation', 'App\EventProduct');
    }
}
