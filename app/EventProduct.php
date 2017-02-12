<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventProduct extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event_product';

    /**
     * Get the event record associated with the EventProduct.
     */
    public function event()
    {
        return $this->belongsTo('App\Event');
    }

    /**
     * Get the product record associated with the EventProduct.
     */
    public function product()
    {
        return $this->belongsTo('App\Product');
    }

    /**
     * Get the participations for the EventProduct.
     */
    public function participations()
    {
        return $this->hasMany('App\Participation', 'event_product_id');
    }
}
