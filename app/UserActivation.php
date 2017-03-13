<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserActivation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id, token'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
      'id', 'created_at', 'updated_at'
    ];

    /**
     * Get the user record associated with the order.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Create un activation token
     */
    protected function getToken()
    {
        return hash_hmac('sha256', str_random(40), config('app.key'));
    }

    public function createActivation($user)
    {

        $activation = $this->getActivation($user);

        if (!$activation) {
            return $this->createToken($user);
        }
        return $this->regenerateToken($user);
    }

    private function regenerateToken($user)
    {
        $token = $this->getToken();
        $this->db->table($this->table)->where(
            'user_id', $user->id)->update([
            'token' => $token,
            'created_at' => new Carbon()
        ]);
        return $token;
    }

    private function createToken($user)
    {
        $token = $this->getToken();
        $this->db->table($this->table)->insert([
            'user_id' => $user->id,
            'token' => $token,
            'created_at' => new Carbon()
        ]);
        return $token;
    }

    public function getActivation($user)
    {
        return $this->db->table($this->table)->where('user_id', $user->id)->first();
    }

    public function getActivationByToken($token)
    {
        return $this->db->table($this->table)->where('token', $token)->first();
    }

    public function deleteActivation($token)
    {
        $this->db->table($this->table)->where('token', $token)->delete();
    }
}
