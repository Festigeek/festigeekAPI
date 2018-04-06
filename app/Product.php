<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use phpDocumentor\Reflection\Types\Boolean;

class Product extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'data'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * The attributes added to the model.
     *
     * @var array
     */
    protected $appends = [

    ];

    /////////////////////

    /**
     * Get the user record associated with the order.
     */
    public function product_type()
    {
        return $this->belongsTo('App\ProductType');
    }

    /**
     * Get the event of the product.
     */
    public function event()
    {
        return $this->belongsTo('App\Event');
    }

    /**
     * Get the products of the order.
     */
    public function orders()
    {
        return $this->belongsToMany('App\Order')->withPivot('amount', 'data', 'consume');
    }

    /**
     * Return the real number of unit sold for this product (better than "sold" field)
     *
     * @param boolean $confirmed Add only paid orders
     * @return integer Sum of product (unit) sold
     */
    public function sales($confirmed = false) {
        return $this->orders()->get()->reduce(function($acc, $order) use ($confirmed) {
            return (!$confirmed || ($confirmed && $order->state == 1)) ? $acc + $order->pivot->amount : $acc;
        }, 0);
    }
}
