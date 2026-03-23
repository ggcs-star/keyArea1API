<?php
namespace App\Models;

use MongoDB\Laravel\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mongodb';
    protected $table = 'admin_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'role',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function hasRole($role)
    {
        return $this->role === $role;
    }


    public function getRolePermissions()
    {
        static $cachedRole = null;

        if ($cachedRole === null) {
            $cachedRole = Role::where('name', $this->role)->first();
        }

        return $cachedRole->permissions ?? [];
    }

    public function hasPermission($permission)
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        if (in_array($permission, $this->permissions ?? [])) {
            return true;
        }

        return in_array($permission, $this->getRolePermissions());
    }
}
