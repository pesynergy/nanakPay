<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DefaultPermission extends Model
{
    protected $fillable = ['permission_id', 'role_id'];
    public $timestamps = false;
}
