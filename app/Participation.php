<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Participation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'team_user';

    /**
     * Get the EventProduct record associated with the Participation.
     */
    public function event_product()
    {
        return $this->belongsTo('App\EventProduct', 'event_product_id');
    }

    /**
     * Get the team record associated with the participation.
     */
    public function team()
    {
        return $this->belongsTo('App\Team');
    }
}
