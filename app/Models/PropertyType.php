<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Str;

class PropertyType extends Model
{
    protected $collection = 'property_types';
    protected $primaryKey = '_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'created_by_id',
        'created_by_type',
        'category_ids',
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'meta_data',
        'status',
    ];

    protected $casts = [
        'meta_data' => 'array',
        'category_ids' => 'array',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
   

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->slug && $model->name) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function createdBy()
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', '_id');
    }
}