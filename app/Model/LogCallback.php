<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LogCallback extends Model
{
    protected $fillable = ['url','modal', 'txnid', 'header', 'request', 'response'];

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
