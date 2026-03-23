<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Amenity extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'amenities';

    protected $fillable = [
        'created_by_id',
        'created_by_type',
        'name',
        'icon_url',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'meta_data',
        'status',
    ];

    protected $casts = [
        'meta_data'  => 'array',
        'status'     => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    
    public function createdBy()
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by_id');
    }
}