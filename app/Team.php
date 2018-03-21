<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes;

    private $caracteres = array(
        'À' => 'a', 'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', '@' => 'a',
        'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', '€' => 'e',
        'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Ö' => 'o', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o',
        'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'µ' => 'u',
        'Œ' => 'oe', 'œ' => 'oe', '$' => 's'
    );

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'pivot'
    ];

    /**
     * The attributes added to the model.
     *
     * @var array
     */
    protected $appends = [
        'users'
    ];

    /**
     * Define model event callbacks.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $keys = array_merge(range('A','Z'), range('0', '9'));

            do {
                $rand_keys = array_rand($keys, 5);
                $code = "";

                foreach ($rand_keys as $k) {
                    $code .= $keys[$k];
                }
            } while(static::where('code', '=', $code)->exists());

            $model->code = $code;
        });
    }

    /**
     * Get the EventProduct for the Team.
     */
    public function orders()
    {
        return $this->belongsToMany('App\Order', 'team_user')
            ->withPivot('captain')
            ->withTimestamps();
    }

    /**
     * Get the users of the team.
     */
    public function users()
    {
        return $this->belongsToMany('App\User')->withPivot('captain')->withTimestamps();
    }

    /**
     * Get the default product of the team (first product of type "tournament", in the first order).
     */
    public function defaultProduct()
    {
        return $this->orders()->first()->products->where('product_type_id', 1)->first();
    }

    /**
     * Set the team's alias.
     *
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $chaine = strtr($this->attributes['name'], $this->caracteres);
        $chaine = preg_replace('/[^A-Za-z0-9]+/', '', $chaine);

        $this->attributes['alias'] = strtolower($chaine);
    }

    /**
     * Return a simplified users array for each teams
     *
     * @return String
     */
    public function getUsersAttribute() {
        // TODO write different value if user is in the team (or admin) maybe create user->isInTeam($team_id) ?
//        $users = $this->users()->get(['username', 'gender'])->makeHidden(['QRCode', 'pivot']);

        // Get correct users for a specific team
        $orders = $this->orders()->where('state', '<>', 3)->get();
        $users = $orders->map(function($order) {
            $roaster = $order->products()->where('product_type_id', 1)->first()->id == $this->defaultProduct()->id;
            $user = [
                'username' => $order->user->username,
                'gender' => $order->user->gender,
                'roaster' => $roaster
            ];

            // Add some informations for members of the team
            if (auth()->user() && $this->hasUser(auth()->user()->id)) {
                $user['firstname'] = $order->user->firstname;
                $user['lastname'] = $order->user->lastname;
                $user['email'] = $order->user->email;
            }

            return $user;
        });

        return $users;
    }

    /**
     * Check if a given user is a member of this team
     *
     * @return bool
     */
    private function hasUser($user_id) {
        return $this->users()->get()->contains('id', $user_id);
    }
}
