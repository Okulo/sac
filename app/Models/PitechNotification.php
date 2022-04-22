<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PitechNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'data',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
