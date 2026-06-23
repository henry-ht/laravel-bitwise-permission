<?php

namespace HenryHt\BitwisePermission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    public function getTable(): string
    {
        return config('bitwise-permission.table_prefix', 'bwp_') . 'permissions';
    }

    protected $fillable = ['name', 'access'];

    protected $casts = ['access' => 'integer'];

    public function accesses(): HasMany
    {
        return $this->hasMany(Access::class);
    }

    // ─── Helpers bitwise ─────────────────────────────────────

    public function allows(string $bitName): bool
    {
        $bit = config("bitwise-permission.bits.{$bitName}", 0);
        return $bit > 0 && ($this->access & $bit) === $bit;
    }

    public function canView(): bool        { return $this->allows('view'); }
    public function canViewAny(): bool     { return $this->allows('view') && $this->allows('view_any'); }
    public function canCreate(): bool      { return $this->allows('view') && $this->allows('create'); }
    public function canUpdate(): bool      { return $this->allows('view') && $this->allows('update'); }
    public function canDelete(): bool      { return $this->allows('view') && $this->allows('delete'); }
    public function canRestore(): bool     { return $this->allows('view') && $this->allows('restore'); }
    public function canForceDelete(): bool { return $this->allows('view') && $this->allows('force_delete'); }
    public function canChangeStatus(): bool{ return $this->allows('view') && $this->allows('change_status'); }
    public function canAssign(): bool      { return $this->allows('view') && $this->allows('assign'); }
    public function canSupport(): bool     { return $this->allows('view') && $this->allows('support'); }

    /**
     * Retorna array con los nombres de bits activos.
     */
    public function activeBits(): array
    {
        $bits = config('bitwise-permission.bits', []);
        return array_keys(array_filter($bits, fn($bit) => ($this->access & $bit) === $bit));
    }
}
