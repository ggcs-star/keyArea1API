<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Str;

class UnitType extends Model
{
    protected $collection = 'unit_types';

    protected $primaryKey = '_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'created_by_id',
        'created_by_type',
        'name',
        'slug',
        'bhk',
        'description',
        'room_sizes',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'meta_data',
        'status',
    ];

   

    public function createdBy()
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by_id');
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            if (!$model->slug && $model->name) {
                $model->slug = Str::slug($model->name);
            }

        });
    }
}