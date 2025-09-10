<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = ['name', 'recharge1', 'recharge2', 'api_id', 'type', 'status', 'paramname', 'range1', 'range2'];

    public $timestamps = false;

    public function getParamnameAttribute($value)
    {
    	return explode(",", $value);
    }

    public function api(){
        return $this->belongsTo('App\Model\Api');
    }
}
