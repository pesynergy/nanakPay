<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayinTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'txnid',
        'payin_ref',
        'order_id',
        'customer_name',
        'amount',
        'mobile',
        'email',
        'device_info',
        'udf',
        'status',
        'bank_ref_num',
        'rrn',
        'payload',
        'response'
    ];

    protected $casts = [
        'udf' => 'array',
        'payload' => 'array',
        'response' => 'array',
        'amount' => 'decimal:2',
    ];
}