<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerLog extends Model
{
    use HasFactory, ModelBase;

    protected $fillable = [
        'customer_id',
        'transaction_id',
        'status',
        'message',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
