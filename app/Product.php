<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'data'
    ];

    /**
     * Get the user record associated with the order.
     */
    public function product_type()
    {
        return $this->belongsTo('App\ProductType');
    }

    /**
     * Get the products of the order.
     */
    public function orders()
    {
        return $this->belongsToMany('App\Order')->withPivot('amount', 'data');
    }

    /**
     * Get the products of the order.
     */
    public function events()
    {
        return $this->belongsToMany('App\Event')->withPivot('price', 'quantity_max', 'sold')->withTimestamps();
    }
}
