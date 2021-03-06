<?php

namespace App;

use Hash;
use Crypt;

use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;

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
        'QRCode',
        'roles'
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
     * Get the teams of the user.
     */
    protected function teams()
    {
        return $this->belongsToMany('App\Team')->withPivot('captain')->withTimestamps();
    }

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany('App\Order');
    }

    /**
     * MUTATOR: Passwords must always be hashed
     *
     * @param $password
     */
    public function setPasswordAttribute($password)
    {
        // check if the value is already a hash
        // (Regex: String begins with '$2y$##$' followed by at least 50 characters)
        if (preg_match('/^\$2y\$[0-9]*\$.{50,}$/', $password)){
            $this->attributes['password'] = $password;
        }
        else {
            $this->attributes['password'] = Hash::make($password);
        }
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

    public function getRolesAttribute() {
        return $this->roles()->get();
    }
}
