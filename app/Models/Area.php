<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Area extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'areas';

    protected $fillable = [
        'state_id',
        'city_id',
        'name',
        'pincode',
        'latitude',
        'longitude',
        'image',
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

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', '_id');
    }


    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', '_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'area_id', '_id');
    }
}