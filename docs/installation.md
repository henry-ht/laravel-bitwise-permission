# Installation

## Requirements

- PHP 8.2+
- Laravel 11, 12 or 13
- Livewire 3 or 4 — only required if you use the bundled admin UI

## 1. Require the package

```bash
composer require henry-ht/laravel-bitwise-permission
```

## 2. Run the installer

```bash
php artisan bwp:install
```

This single command:

1. Publishes `config/bitwise-permission.php`.
2. Publishes the migration that creates the package tables.
3. Publishes the package's CSS assets.
4. Runs `php artisan migrate` (with a confirmation prompt, unless `--migrate` is passed).
5. Seeds base roles, permissions, routes and menus (with a confirmation prompt, unless `--seed` is passed).

Non-interactive install, useful in CI or deploy scripts:

```bash
php artisan bwp:install --migrate --seed --force
```

| Option | Description |
|---|---|
| `--migrate` | Run migrations without prompting |
| `--seed` | Seed base data without prompting |
| `--force` | Overwrite already-published files |

## Manual installation

If you'd rather run each step yourself:

```bash
php artisan vendor:publish --tag=bwp-config
php artisan vendor:publish --tag=bwp-migrations
php artisan vendor:publish --tag=bwp-assets
php artisan migrate
php artisan db:seed --class="HenryHt\BitwisePermission\Database\Seeders\BitwisePermissionSeeder"
```

Or publish everything in one go:

```bash
php artisan vendor:publish --tag=bwp-all
```

## Wiring up the `User` model

### Add `role_id` to the `users` table

```bash
php artisan make:migration add_role_id_to_users_table --table=users
```

```php
// up()
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('role_id')
          ->nullable()
          ->constrained('bwp_roles')
          ->nullOnDelete();
});

// down()
Schema::table('users', function (Blueprint $table) {
    $table->dropConstrainedForeignId('role_id');
});
```

> If you changed `table_prefix` in the config, reference `{prefix}roles` instead of `bwp_roles`.

### Add the trait

```php
use HenryHt\BitwisePermission\Traits\HasPermissionsTrait;

class User extends Authenticatable
{
    use HasPermissionsTrait;

    protected $fillable = ['name', 'email', 'password', 'role_id'];
}
```

The trait registers everything: `can*()` checks, `isSuperAdmin()`, permission and menu management. See [Permissions](permissions.md) for the full method list.

### Middleware is registered automatically

The package registers the `bwp.permission` middleware alias for you — nothing to add to `bootstrap/app.php`. Use it directly on your routes:

```php
Route::middleware('bwp.permission')->group(function () {
    // protected routes
});
```

To use a different alias, change it in the config:

```php
'middleware' => [
    'alias' => 'bwp.permission',
],
```

See [Middleware](middleware.md) for how the `view` prerequisite and additional bits work.

## Next steps

1. Define `bits`, `base_permissions` and `super_admin_role` in the config — see [Configuration](configuration.md).
2. Define `role_permissions` per role.
3. Define who can access the admin UI via `gate`.
4. Run `php artisan bwp:sync-routes` to register your project's named routes.
5. Run `php artisan db:seed` again if you added roles, permissions or menus.
6. Visit `/bwp/accesses` to review access per role, and `/bwp/menus/roles` to configure menu visibility.

For a complete walk-through, see [Examples](examples.md).
