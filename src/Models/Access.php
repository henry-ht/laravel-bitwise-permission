<?php

namespace HenryHt\BitwisePermission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Access extends Model
{
    public function getTable(): string
    {
        return config('bitwise-permission.table_prefix', 'bwp_') . 'accesses';
    }

    protected $fillable = [
        'role_id',
        'route_id',
        'permission_id',
    ];

    protected $hidden = ['role_id', 'route_id', 'permission_id'];

    // Eager load permission siempre — evita N+1 al verificar permisos
    protected $with = ['permission'];

    public function role(): BelongsTo      { return $this->belongsTo(Role::class); }
    public function route(): BelongsTo     { return $this->belongsTo(AppRoute::class, 'route_id'); }
    public function permission(): BelongsTo{ return $this->belongsTo(Permission::class); }

    public function scopeForRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId);
    }
}
