<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUser extends Model
{
    use HasFactory;
    protected $table = 'product_user';

    protected $fillable = [
        'product_id',
        'user_id',
        'stake',
        'employment_at',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
