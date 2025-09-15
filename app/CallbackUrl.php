<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CallbackUrl extends Model
{

    protected $table = 'callback_urls';

    protected $fillable = [
        'payin_callback',
        'payout_callback',
    ];
}