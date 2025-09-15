<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallbackUrl extends Model
{
    use HasFactory;
    protected $table = 'callback_urls';

    protected $fillable = [
        'payin_callback',
        'payout_callback',
    ];
}