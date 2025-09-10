<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PortalSetting extends Model
{
    protected $fillable = ['name', 'code', 'value', 'company_id'];
    public $timestamps = false;
}
