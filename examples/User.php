<?php

namespace App\Models;

use HenryHt\BitwisePermission\Traits\HasPermissionsTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Minimal User model wired up for laravel-bitwise-permission.
 *
 * See: https://bitwise.tchenry.com/docs/installation
 */
class User extends Authenticatable
{
    use HasPermissionsTrait;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
