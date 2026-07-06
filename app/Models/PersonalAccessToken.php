<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use MongoDB\Laravel\Eloquent\Model;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $connection = 'mongodb';
    protected $collection = 'personal_access_tokens';
    
    // Override the table name
    public function getTable()
    {
        return $this->collection;
    }
    
    // Override the key type
    protected $keyType = 'string';
    public $incrementing = false;
    
    // The attributes that should be cast to native types.
    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}