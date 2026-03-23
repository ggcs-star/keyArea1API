<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Role extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'admin_roles';

    protected $fillable = [
        'name',
        'permissions'
    ];
}
