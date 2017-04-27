<?php
//
//namespace App;
//
//use Illuminate\Database\Eloquent\Model;
//
//class Address extends Model
//{
//    /**
//     * The attributes that are not mass assignable.
//     *
//     * @var array
//     */
//    protected $guarded = ['id'];
//
//    /**
//     * The attributes excluded from the model's JSON form.
//     *
//     * @var array
//     */
//    protected $hidden = [
//        'user_id', 'updated_at', 'created_at'
//    ];
//
//    /**
//     * Get the user record associated with the address.
//     */
//    public function user()
//    {
//        return $this->belongsTo('App\User');
//    }
//
//    /**
//     * Get the country of the address.
//     */
//    public function country()
//    {
//        return $this->belongsTo('App\Country');
//    }
//}
