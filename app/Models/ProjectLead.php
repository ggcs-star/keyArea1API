<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ProjectLead extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'email',
        'country_code',
        'mobile',
        'looking_for',
        'preferred_bedrooms',
        'consent',
        'ip_address',
        'user_agent',
        'source',
        'status',
    ];
}