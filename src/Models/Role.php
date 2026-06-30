<?php

namespace HenryHt\BitwisePermission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'base_role_id',
    ];

    protected $casts = [
        'is_base_role' => 'boolean',
        'base_role_id' => 'integer',
    ];

    public function accesses(): HasMany
    {
        return $this->hasMany(Access::class);
    }

    // public function user(){
    //     return $this->hasOne(config('bitwise-permission.user_model'));
    // }

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
 
    /**
     * Rol base del cual se clonó este rol (null si es base o no clonado).
     */
    public function baseRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'base_role_id');
    }
 
    /**
     * Roles de usuario clonados a partir de este rol base.
     */
    public function clonedRoles(): HasMany
    {
        return $this->hasMany(Role::class, 'base_role_id');
    }

    public function scopeBase($query)
    {
        return $query->where('is_base_role', true);
    }
 
    public function scopeClonedFrom($query, int $baseRoleId)
    {
        return $query->where('base_role_id', $baseRoleId);
    }
}
