<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Configuration extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'configurations';
    protected $primaryKey = '_id';

    protected $fillable = [
        'created_by_id',
        'created_by_type',
        'category_ids',
        'name',
        'type',
        'type_size',
        'total_configuration_size',
        'room_sizes',
        'description',
        'status',
    ];

    protected $casts = [
        'category_ids' => 'array',
        'room_sizes' => 'array',
        'status' => 'boolean',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;



    public function createdBy()
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by_id');
    }

    public function categories()
    {
        return Category::whereIn('_id', $this->category_ids ?? [])->get();
    }
}