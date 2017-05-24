<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
