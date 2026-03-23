<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Promoter extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'promoters';

    protected $fillable = [
        'created_by_id',
        'created_by_type',
        'name',
        'email',
        'phone',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

   
    public function createdBy()
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by_id');
    }
}