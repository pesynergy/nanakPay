<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Upiload extends Model
{
	protected $fillable = ['payername', 'payerupi', 'txnid', 'upiid', 'refid', 'utr', 'amount', 'status', 'upi_string', 'user_id', 'type', 'callback','api_id', 'apitxnid'];

    public $appends = ['username'];

    public function getUsernameAttribute()
    {
        $data = '';
        if($this->user_id){
            $user = \App\User::where('id' , $this->user_id)->first(['name', 'id', 'role_id']);
            $data = $user->name." (".$user->id.") <br>(".$user->role->name.")";
        }
        return $data;
    }
}
