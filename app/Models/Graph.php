<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Graph extends Model
{
    protected $table = 'graphs';

    protected $fillable = [
        'name',
        'type',
        'description',
        'color',
        'order',
        'chart_id',
        'is_visible',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function statistics()
    {
        return $this->hasMany(StatisticsModel::class, 'graph_id');
    }

    public function charts()
    {
        return $this->belongsToMany(Chart::class);
        // return $this->hasMany(Graph::class, 'chart_id')->orderBy('order');
    }
}
