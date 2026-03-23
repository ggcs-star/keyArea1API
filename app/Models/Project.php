<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Project extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'projects';
    protected $primaryKey = '_id';

    protected $fillable = [

        'created_by_id',
        'created_by_type',

        'builder_id',
        'promoter_id',
        'property_type_ids',
        'unit_type_ids',
        'category_ids',
        'configuration_ids',
        'amenity_ids',
        'tower_ids',

        'name',
        'slug',
        'project_type',

        'description',
        'short_description',
        'address',

        'state_id',
        'city_id',
        'area_id',
        'State_name',
        'city_name',
        'area_name',
        'pincode',

        'latitude',
        'longitude',

        'rera_number',
        'possession_date',
        'launch_date',
        'project_status',

        'cover_image_url',
        'gallery_images_url',
        'floorPlans_images_url',
        'slider_image_url',
        'brochure_url',
        'reel_url',

        'price',

        'carpet_area',

        'total_units',
        'total_towers',

        'meta_title',
        'meta_description',
        'meta_keywords',

        'meta_data',

        'is_featured',
        'is_emerging_property',
        'is_emerging_area',
        'is_new_launch',
        'is_trending',

        'display_order',
        'status',
    ];

    protected $casts = [

        // Arrays
        'category_ids' => 'array',
        'configuration_ids' => 'array',
        'amenity_ids' => 'array',
        'tower_ids' => 'array',
        'property_type_ids' => 'array',
        'unit_type_ids' => 'array',
        'gallery_images_url' => 'array',
        'floorPlans_images_url' => 'array',
        'slider_image_url' => 'array',

        'meta_data' => 'array',

        // Numbers
        'latitude' => 'float',
        'longitude' => 'float',

        'total_units' => 'integer',
        'total_towers' => 'integer',
        'total_floors' => 'integer',

        'display_order' => 'integer',

        // Dates
        'possession_date' => 'datetime',
        'launch_date' => 'datetime',

        // Boolean Flags
        'is_featured' => 'boolean',
        'is_emerging_property' => 'boolean',
        'is_emerging_area' => 'boolean',
        'is_new_launch' => 'boolean',
        'is_trending' => 'boolean',
        'status' => 'boolean',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;


    public function builder()
    {
        return $this->belongsTo(BuilderUser::class, 'builder_id', '_id');
    }

    public function promoter()
    {
        return $this->belongsTo(Promoter::class, 'promoter_id', '_id');
    }

    public function createdBy()
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', '_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', '_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', '_id');
    }


    public function amenities()
    {
        return Amenity::whereIn('_id', $this->amenity_ids ?? [])->get();
    }

    public function categories()
    {
        return Category::whereIn('_id', $this->category_ids ?? [])->get();
    }

   

    public function towers()
    {
        return $this->hasMany(Tower::class, 'project_id', '_id');
    }

    // public function towers()
    // {
    //     return Tower::whereIn('_id', $this->tower_ids ?? [])->get();
    // }
}