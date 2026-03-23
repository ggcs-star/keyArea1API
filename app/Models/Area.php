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
        'status',
    ];

    protected $casts = [
        'latitude'   => 'float',
        'longitude'  => 'float',
        'status'     => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    /**
     * Area belongs to State
     */
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', '_id');
    }

    /**
     * Area belongs to City
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', '_id');
    }
}