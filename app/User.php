<?php

namespace App;

use Hash;
use Crypt;

use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Laratrust\Traits\LaratrustUserTrait;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, LaratrustUserTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'birthdate',

        'gender',
        'firstname',
        'lastname',
        'country_id',
        'street',
        'street2',
        'npa',
        'city',

        'lol_account',
        'steamID64',
        'battleTag'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'drupal_password',
        'registration_token',
        'drupal_id',
        'activated'
    ];

    /**
     * The attributes added to the model.
     *
     * @var array
     */
    protected $appends = [
//        'address',
        'QRCode'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'activated' => 'boolean',
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

//    /**
//     * Get the addresses for the user. (if many used)
//     */
//    protected function addresses()
//    {
//        return $this->hasMany('App\Address');
//    }

    /**
     * Get the teams of the user.
     */
    protected function teams()
    {
        return $this->belongsToMany('App\Team')->withPivot('captain')->withTimestamps();
    }

    /**
     * MUTATOR: Passwords must always be hashed
     *
     * @param $password
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    /**
     * Create an activation token
     */
    protected function getActivationToken($value)
    {
        $value = (is_null($value))?str_random(40):$value . str_random(10);
        return hash_hmac('sha256', $value, config('app.key'));
    }

    /**
     * Generates the value for the User::registration_token field. Used to
     * activate the user's account.
     * @return bool
     */
    protected function generateRegistrationToken()
    {
        if(!$this->activated)
            $this->attributes['registration_token'] = self::getActivationToken($this->email);
        else
            return true;

        if(is_null($this->attributes['registration_token']))
            return false;
        else
            return true;
    }

    /**
     * Boot function for using with User Events
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model)
        {
            $model->generateRegistrationToken();
        });
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Return a base_64 generated qrcode based on user informations
     *
     * @return String
     */
    public function getQRCodeAttribute() {
        $payload = Crypt::encrypt("{\"id\": " . $this->id . ", \"birthdate\": " . $this->birthdate . "}");

        QrCode::format('png');
        QrCode::size(300);
        //QrCode::errorCorrection('H');
        QrCode::margin(0);
        QrCode::backgroundColor(250,250,250);
        return base64_encode(QrCode::encoding('UTF-8')->merge('/public/images/logo_carre.jpg', .2)->generate($payload));
    }

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany('App\Order');
    }


//    /**
//     * Get the first address for the user or instantiate an empty one.
//     */
//    public function getAddressAttribute()
//    {
//        return $this->addresses()->first();
//    }


    /*
     * ===============================================================================================================
     * ===============================================================================================================
     * TODO: Part to rewrite
     * ===============================================================================================================
     * ===============================================================================================================
     */
    //

    //
    // /**
    //  * Get the inscriptions for the user
    //  * TODO: moche, très moche. dès qu'on a fait le checkin --> vérifier used dans abstract_product à 1
    //  */
    // public function inscriptions($userId)
    // {
    //     // Tableau des inscriptions
    //    // return $this->hasMany('App\Order');
    //     //return $this->morphedByMany('App\Inscription', 'abstract_products', 'orders')->withPivot('used');
    //
    //
    //     $inscriptions = DB::table('users')
    //         ->select('abstract_products.*')
    //         ->join('orders', 'users.id', '=', 'orders.user_id') //bon
    //         ->join('abstract_products', 'abstract_products.order_id', '=', 'orders.id')
    //
    //        ->join('inscriptions', 'abstract_products.abstract_products_id', '=', 'inscriptions.id')
    //         ->where('users.id', '=', $userId)
    //         ->where('abstract_products.abstract_products_type', "=", "App\\Inscription")
    //        // ->groupBy('inscriptions.id')
    //         ->get();
    //
    //     return $inscriptions;
    // }
}
