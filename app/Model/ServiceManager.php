<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ServiceManager extends Model
{
    protected $fillable = ['api_id', 'provider_id', 'user_id'];
    
    public $timestamps = false;
    public $with = ['provider', 'api'];

    public function provider(){
        return $this->belongsTo('App\Model\Provider');
    }

    public function api(){
        return $this->belongsTo('App\Model\Api');
    }
}
