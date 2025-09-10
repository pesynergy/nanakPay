<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Api extends Model
{
    protected $fillable = ['product', 'name', 'url', 'username', 'password', 'optional1', 'status', 'code', 'type', 'gst', 'tds'];
}
