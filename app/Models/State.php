<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class State extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'states';

    protected $fillable = [
        'name',
        'code',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;
    public function cities()
    {
        return $this->hasMany(City::class, 'state_id', '_id');
    }

    public function areas()
    {
        $cityIds = $this->cities()->pluck('_id');

        return Area::whereIn('city_id', $cityIds)->get();
    }
}