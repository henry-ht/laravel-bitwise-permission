<?php

namespace HenryHt\BitwisePermission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public function getTable(): string
    {
        return config('bitwise-permission.table_prefix', 'bwp_') . 'roles';
    }

    protected $fillable = [
        'name',
        'public_name',
        'description',
        'is_base_role',
    ];

    protected $casts = [
        'is_base_role' => 'boolean',
    ];

    public function accesses(): HasMany
    {
        return $this->hasMany(Access::class);
    }

    public function menus(): BelongsToMany
    {
        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        return $this->belongsToMany(Menu::class, "{$prefix}menu_role")
                    ->withPivot(['id', 'disabled'])
                    ->withTimestamps();
    }

    public function routes(): BelongsToMany
    {
        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        return $this->belongsToMany(AppRoute::class, "{$prefix}accesses", 'role_id', 'route_id')
                    ->withPivot(['id', 'permission_id'])
                    ->withTimestamps();
    }

    public function scopeBase($query)
    {
        return $query->where('is_base_role', true);
    }
}
