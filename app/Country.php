<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    /**
     * Get the adresses for the country.
     */
    public function addresses()
    {
        return $this->hasMany('App\Address');
    }
}
