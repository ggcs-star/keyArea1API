<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Tower extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'towers';

    protected $primaryKey = '_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [

        'project_id',
        'name',
        'type',
        'units',
        'total_floors',
        'total_units',
        'floor_designs',
        'unit_ranges',
        'status',
    ];

    protected $casts = [

        'units' => 'array',
        'floor_designs' => 'array',

        'total_floors' => 'integer',
        'total_units' => 'integer',

        'status' => 'boolean',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', '_id');
    }

  

public function getUnitTypeIds()
{
    $floorTypes = collect($this->floor_designs ?? [])
        ->pluck('unit_type_id');

    $rangeTypes = collect($this->unit_ranges ?? [])
        ->pluck('unit_type_id');

    return $floorTypes
        ->merge($rangeTypes)
        ->filter()
        ->unique()
        ->values()
        ->toArray();
}


  public function getPropertyTypeIds()
{
    $floorTypes = collect($this->floor_designs ?? [])
        ->pluck('property_type_id');

    $rangeTypes = collect($this->unit_ranges ?? [])
        ->pluck('property_type_id');

    return $floorTypes
        ->merge($rangeTypes)
        ->filter()
        ->unique()
        ->values()
        ->toArray();
}


    public function getFloorDesignsAttribute($value)
{
    return is_string($value) ? json_decode($value, true) : $value;
}

public function getUnitsAttribute($value)
{
    return is_string($value) ? json_decode($value, true) : $value;
}

public function getUnitRangesAttribute($value)
{
    return is_string($value) ? json_decode($value, true) : $value;
}

}