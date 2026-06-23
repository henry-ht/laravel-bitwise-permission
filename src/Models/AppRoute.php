<?php

namespace HenryHt\BitwisePermission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppRoute extends Model
{
    public function getTable(): string
    {
        return config('bitwise-permission.table_prefix', 'bwp_') . 'app_routes';
    }

    protected $fillable = [
        'name',
        'type',
        'patch',
        'base_url',
        'description',
    ];

    public function accesses(): HasMany
    {
        return $this->hasMany(Access::class, 'route_id');
    }

    public function roles(): BelongsToMany
    {
        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        return $this->belongsToMany(Role::class, "{$prefix}accesses", 'route_id', 'role_id')
                    ->withPivot(['id', 'permission_id'])
                    ->withTimestamps();
    }

    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }
}
