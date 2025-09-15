<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Apitoken extends Model
{

    protected $fillable = ['token', 'ip', 'user_id', 'status', 'upicallbackurl', 'payoutcallbackurl'];
    public $appends     = ['username'];
    public function getUsernameAttribute()
    {
        $data = '';
        if ($this->user_id) {
            $user = \App\User::where('id', $this->user_id)->first(['name', 'id', 'role_id']);
            $data = $user->name . " (" . $user->id . ")";
        }
        return $data;
    }
}