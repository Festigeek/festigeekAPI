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
        return $this->hasMany('App\Product');
    }

    /**
     * Get all the orders for the event.
     */
    public function orders()
    {
        return Order::where('event_id', $this->id);
    }

    /**
     * Get all the teams for the event.
     */
    public function teams(){
        $orders = Order::where('event_id', $this->id)->get();

        return $orders->map( function($order) {
            return $order->team()->get();
        })->flatten()->filter(function($value) {
            return !is_null($value);
        })->unique('id')->sortBy('name')->values();
    }
}
