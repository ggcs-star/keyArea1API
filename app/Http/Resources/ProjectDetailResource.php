<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\BuilderUser;
use App\Models\UnitType;
use App\Models\Amenity;
use App\Models\Category;

class ProjectDetailResource extends JsonResource
{
    public function toArray($request)
    {
        $promoters = BuilderUser::whereIn('_id', $this->promoter_ids ?? [])->get();
        $unitTypes = UnitType::whereIn('_id', $this->unit_type_ids ?? [])->get();
        $amenities = Amenity::whereIn('_id', $this->amenity_ids ?? [])->get();
        $categories = Category::whereIn('_id', $this->category_ids ?? [])->get();

        return [

            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'project_type' => $this->project_type,
            'project_status' => $this->project_status,

            'builder' => $this->builder ? [
                'id' => $this->builder->_id,
                'name' => $this->builder->name,
                'company_name' => $this->builder->company_name,
                'email' => $this->builder->email,
                'phone' => $this->builder->phone,
                'address' => $this->builder->address,
                'logo' => $this->builder->logo,
            ] : null,

            'promoters' => $promoters,

            'description' => $this->description,
            'short_description' => $this->short_description,
            'address' => $this->address,

            'state' => optional($this->state)->name,
            'city' => optional($this->city)->name,
            'area' => optional($this->area)->name,
            'pincode' => $this->pincode,

            'latitude' => $this->latitude,
            'longitude' => $this->longitude,

            'rera_number' => $this->rera_number,
            'carpet_area' => $this->carpet_area,
            'total_units' => $this->total_units,
            'total_towers' => $this->total_towers,
            'launch_date' => $this->launch_date,
            'possession_date' => $this->possession_date,

            'cover_image' => $this->cover_image_url,
            'slider_images' => $this->slider_image_url,
            'gallery_images' => $this->gallery_images_url,
            'floor_plans' => $this->floorPlans_images_url,
            'brochure' => $this->brochure_url,
            'reel' => $this->reel_url,

            'unit_types' => $unitTypes,
            'amenities' => $amenities,
            'categories' => $categories,
            // 'towers' => $this->towers,

            'is_featured' => $this->is_featured,
            'is_trending' => $this->is_trending,
            'is_new_launch' => $this->is_new_launch,
            'is_emerging_property' => $this->is_emerging_property,
            'is_emerging_area' => $this->is_emerging_area,

            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'meta_data' => $this->meta_data,

            'display_order' => $this->display_order,
            'status' => $this->status,
        ];
    }
}