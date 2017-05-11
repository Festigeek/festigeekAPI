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
        'user_id', 'data', 'event_id', 'state', 'payment_type_id', 'paypal_paymentId'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'data'
    ];

    /**
     * The attributes added to the model.
     *
     * @var array
     */
    protected $appends = [
        'user', 'team', 'products', 'payment_type'
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
        return $this->belongsToMany('App\Team', 'team_user')->withPivot('captain', 'user_id')->withTimestamps();
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

    /**
     * Return the user entry
     *
     * @return String
     */
    public function getUserAttribute() {
        return $this->user()->first()->makeHidden(['QRCode', 'lang', 'street2', 'updated_at', 'created_at']);
    }

    /**
     * Return the team entry
     *
     * @return String
     */
    public function getTeamAttribute() {
        return $this->team()->first();
    }

    /**
     * Return the team entry
     *
     * @return String
     */
    public function getPaymentTypeAttribute() {
        return $this->payment_type()->first()->makeHidden(['id', 'updated_at', 'created_at']);
    }

    /**
     * Return a simplified products array for each order
     *
     * @return String
     */
    public function getProductsAttribute() {
        return $this->products()->get(['products.id', 'name', 'price', 'product_type_id'])->makeHidden(['updated_at', 'created_at']);
    }
}
