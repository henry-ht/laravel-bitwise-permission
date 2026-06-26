<?php

namespace HenryHt\BitwisePermission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    public function getTable(): string
    {
        return config('bitwise-permission.table_prefix', 'bwp_') . 'menus';
    }

    protected $fillable = [
        'name',
        'public_name',
        'patch',
        'icon',
        'order',
        'father_id',
    ];

    protected $casts = [
        'order'     => 'integer',
        'father_id' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'father_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'father_id');
    }

    public function childrenOrdered(): HasMany
    {
        return $this->children()->orderBy('order')->with('childrenOrdered');
    }

    public function roles(): BelongsToMany
    {
        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        return $this->belongsToMany(Role::class, "{$prefix}menu_role")
                    ->withPivot(['id', 'disabled'])
                    ->withTimestamps();
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('father_id');
    }

    public function scopeForRole($query, int $roleId)
    {
        $prefix = config('bitwise-permission.table_prefix', 'bwp_');
        $table  = "{$prefix}menu_role";

        return $query->whereExists(function ($q) use ($roleId, $table) {
            $q->select(\DB::raw(1))
            ->from($table)
            ->whereColumn("{$table}.menu_id", 'bwp_menus.id')
            ->where("{$table}.role_id",  $roleId)
            ->where("{$table}.disabled", false);
        });
    }
}
