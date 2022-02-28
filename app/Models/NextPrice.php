<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NextPrice extends Model
{
    use HasFactory, ModelBase;

    protected $fillable = [
        'product_id',
        'price',
        'period',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
