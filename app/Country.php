<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'cc_fips', 'cc_iso', 'tld', 'updated_at', 'created_at'
    ];

    /**
     * Get the adresses for the country.
     */
    public function addresses()
    {
        return $this->hasMany('App\Address');
    }
}
