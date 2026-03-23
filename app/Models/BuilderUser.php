<?php

namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;

class BuilderUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'builders_users';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'company_name',
        'address',
        'logo',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', '_id');
    }

    public function getRolePermissions()
    {
        return $this->role?->permissions ?? [];
    }

    public function hasPermission($permission)
    {
        if ($this->role?->name === 'super_admin') {
            return true;
        }

        return in_array($permission, $this->getRolePermissions());
    }
}