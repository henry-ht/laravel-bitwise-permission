# Changelog

All notable changes to `laravel-bitwise-permission` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

Nothing yet.

## [1.0.0] - 2026-07-09

Initial public release.

### Added

- Bitwise permission core (`BitwiseHelper`): `combine()`, `decode()`, `has()`, `add()`, `remove()`, `total()`, `all()`.
- `HasPermissionsTrait` for the `User` model — `can*()` helpers, `isSuperAdmin()`, `setPermission()`, `setPermissions()`, `getPermissionFor()`, `getAllPermissions()`, `setMenuAccess()`, `setMenuAccesses()`, `hasMenuAccess()`, `getMenu()`.
- `CheckPermissionMiddleware` (`bwp.permission`) with the `view`-as-prerequisite rule and automatic route-name-to-wildcard resolution.
- `CheckBwpUiGateMiddleware` protecting the bundled admin UI via a configurable gate.
- `Role`, `Permission`, `AppRoute`, `Access` and `Menu` Eloquent models, plus `RoleObserver`, `AppRouteObserver` and `MenuObserver`.
- `RoleCloneService` for cloning a base role into a dedicated per-user role.
- Optional Livewire admin UI at `/bwp/roles`, `/bwp/permissions`, `/bwp/routes`, `/bwp/accesses`, `/bwp/menus` and `/bwp/menus/roles`.
- `bwp:install` artisan command — publishes config, migrations and assets, runs migrations, and seeds base data.
- `bwp:sync-routes` artisan command — scans named routes and registers their wildcards automatically.
- Config-driven bits, base permissions, base roles, base routes and base menus, with a super admin bypass that never queries the database.
- Configurable table prefix (`bwp_` by default).

[unreleased]: https://github.com/henry-ht/laravel-bitwise-permission/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/henry-ht/laravel-bitwise-permission/releases/tag/v1.0.0
