<p align="center">
  <img src="https://bitwise.tchenry.com/assets/img/og-image.png" alt="Laravel Bitwise Permission" width="760">
</p>

<h1 align="center">Laravel Bitwise Permission</h1>

<p align="center">
  A fast, database-light permission system for Laravel.<br>
  Roles, permissions, routes and menus — resolved from a single integer, not a join table.
</p>

<p align="center">
  <a href="https://packagist.org/packages/henry-ht/laravel-bitwise-permission"><img src="https://img.shields.io/packagist/v/henry-ht/laravel-bitwise-permission.svg?style=flat-square" alt="Latest Version"></a>
  <a href="https://github.com/henry-ht/laravel-bitwise-permission/actions"><img src="https://img.shields.io/github/actions/workflow/status/henry-ht/laravel-bitwise-permission/tests.yml?branch=main&label=tests&style=flat-square" alt="Tests"></a>
  <a href="https://packagist.org/packages/henry-ht/laravel-bitwise-permission"><img src="https://img.shields.io/packagist/dt/henry-ht/laravel-bitwise-permission.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square" alt="License"></a>
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777bb4.svg?style=flat-square" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-11%20%7C%2012%20%7C%2013-ff2d20.svg?style=flat-square" alt="Laravel">
</p>

<p align="center">
  <b><a href="https://bitwise.tchenry.com">Documentation</a></b> ·
  <a href="https://bitwise.tchenry.com/docs/installation">Installation</a> ·
  <a href="https://bitwise.tchenry.com/docs/examples">Examples</a> ·
  <a href="README.es.md">Español</a>
</p>

---

## Why bitwise?

Most permission packages store one database row per `role × permission × resource`. That works, but it grows fast and every authorization check becomes a query — or a cache you have to invalidate correctly.

A bitwise permission is a single integer. Every capability is a power of two, so any combination of capabilities is just one number:

```php
view (1) + create (4) + update (8) = 13
```

Checking access is a CPU-level `AND` operation, not a database lookup:

```php
$access & $bit === $bit   // true or false, no query
```

Laravel Bitwise Permission builds a complete authorization layer on top of that idea — **roles**, **per-route permissions**, **navigation menus**, and an optional **Livewire admin UI** — while keeping the core check as cheap as it can possibly be.

## Features

- 🔢 **Bitwise core** — combine and compare permissions with plain integer math, no runtime joins.
- 🧩 **Route-based permissions** — protect named routes with a wildcard convention (`leads.*`, `deals.*`).
- 🧭 **Menus with role visibility** — a self-managing navigation tree, including automatic parent/child propagation.
- 👑 **Super admin bypass** — a configured super admin role always has full access, without ever touching the database.
- 🧬 **Per-user roles** — `RoleCloneService` clones a base role per user, so individual permissions can diverge safely.
- 🖥️ **Optional Livewire UI** — manage roles, permissions, routes, accesses and menus from `/bwp/*` out of the box.
- ⚙️ **Artisan tooling** — `bwp:install`, `bwp:sync-routes` and `bwp:sync-base` get a project wired up in minutes.
- 🧱 **Config-first** — bits, base permissions, roles, routes and menus are all defined in one published config file.

## Quick look

```php
use HenryHt\BitwisePermission\Traits\HasPermissionsTrait;

class User extends Authenticatable
{
    use HasPermissionsTrait;
}
```

```php
Route::middleware('bwp.permission:create')->group(function () {
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
});
```

```php
@if(auth()->user()->canCreate())
    <a href="{{ route('leads.create') }}">New lead</a>
@endif

@if(auth()->user()->isSuperAdmin())
    <span>Super Admin</span>
@endif
```

```php
$user->setPermission('leads.*', 'modify access');
$user->setMenuAccess('leads', true);
```

## Installation

```bash
composer require henry-ht/laravel-bitwise-permission
php artisan bwp:install
```

`bwp:install` publishes the config, migrations and assets, runs the migrations, and seeds the base roles, permissions and menus. See the [installation guide](https://bitwise.tchenry.com/docs/installation) for the manual, step-by-step version and for wiring up your `User` model.

### Syncing base data

After the initial install, you can re-sync base roles, routes and menus from the config at any time without duplicating existing records:

```bash
# Sync all (roles + routes + menus)
php artisan bwp:sync-base

# Sync only roles
php artisan bwp:sync-base --roles

# Sync only routes
php artisan bwp:sync-base --routes

# Sync only menus
php artisan bwp:sync-base --menus

# Combine options
php artisan bwp:sync-base --roles --routes
```

`bwp:sync-base` reads the `base_roles`, `base_routes` and `base_menus` arrays from `config/bitwise-permission.php`, creates new entries, updates changed ones and skips records that are already up to date. Menu role visibility (`bwp_menu_role`) is also refreshed automatically.

## Documentation

Full documentation, including configuration reference, middleware usage, role and permission management, menu building, and real-world examples, lives at **[bitwise.tchenry.com](https://bitwise.tchenry.com)**.

| Guide | Description |
|---|---|
| [Installation](https://bitwise.tchenry.com/docs/installation) | Requirements, install command, and manual setup |
| [Configuration](https://bitwise.tchenry.com/docs/configuration) | Bits, base permissions, super admin, table prefix |
| [Middleware](https://bitwise.tchenry.com/docs/middleware) | Protecting routes and the `view` prerequisite rule |
| [Roles](https://bitwise.tchenry.com/docs/roles) | Base roles, per-user roles, `RoleCloneService` |
| [Permissions](https://bitwise.tchenry.com/docs/permissions) | Checking, setting and combining permissions at runtime |
| [Menus](https://bitwise.tchenry.com/docs/menus) | Building navigation trees with role-based visibility |
| [Examples](https://bitwise.tchenry.com/docs/examples) | End-to-end flows: user creation, custom bits, Livewire |
| [Upgrading](https://bitwise.tchenry.com/docs/upgrading) | Version-to-version upgrade notes |

## Requirements

- PHP 8.2+
- Laravel 11, 12 or 13
- Livewire 3 or 4 (only required if you use the bundled admin UI)

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for a history of releases.

## Contributing

Contributions are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request.

## Security

If you discover a security vulnerability, please review [SECURITY.md](SECURITY.md) for our disclosure process — do not open a public issue.

## Credits

- [Henry HT](https://github.com/henry-ht) — [Facebook](https://www.facebook.com/thecreativehenry) · [Instagram](https://www.instagram.com/thecreativehenry/)
- [All contributors](https://github.com/henry-ht/laravel-bitwise-permission/contributors)

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
