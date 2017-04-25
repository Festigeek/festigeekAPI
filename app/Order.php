<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'data'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'user_id'
    ];

    /**
     * Get the user record associated with the order.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the products of the order.
     */
    public function products()
    {
        return $this->belongsToMany('App\Product')->withPivot('amount', 'data');
    }

    /**
     * Get the team associated with the order.
     */
    public function team()
    {
        return $this->belongsToMany('App\Team', 'team_user')->withPivot('captain', 'user_id')->first();
    }

    /**
     * Get the team associated with the order.
     */
    public function payment_type()
    {
        return $this->belongsTo('App\PaymentType');
    }

    /**
     * Get the event_id of the first product of type 'Inscriptions'.
     *
     * @return int event_id
     */
    public function getEventIdAttribute()
    {
        try {
            return $this->products()->whereHas('product_type', function ($query) {
                $query->where('name', 'Inscriptions');
            })->firstOrFail()->event_id;
        }
        catch(\Exception $e) {
            return null;
        }
    }
}
