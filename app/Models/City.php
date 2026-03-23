<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class City extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'cities';

    protected $fillable = [
        'state_id',
        'name',
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
     * Relationship: City belongs to State
     */
    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', '_id');
    }
    public function areas()
{
    return $this->hasMany(Area::class, 'city_id', '_id');
}
}