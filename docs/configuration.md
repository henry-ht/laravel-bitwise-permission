# Configuration

Everything lives in `config/bitwise-permission.php`, published by `bwp:install`.

## Table prefix

```php
'table_prefix' => 'bwp_',
```

Applied to every table the package creates: `bwp_roles`, `bwp_permissions`, `bwp_app_routes`, `bwp_accesses`, `bwp_menus`.

## Bits

Every capability is a power of two. `view` must always be `1` — it's the absolute prerequisite bit (see [Middleware](middleware.md)).

```php
'bits' => [
    'view'   => 1,
    'export' => 2,
    'create' => 4,
    'update' => 8,
    'delete' => 16,
    'modify_access' => 32,
],
```

Add as many custom bits as you need, always as the next power of two. `BitwiseHelper::total()` and the super admin bypass both read this array, so it is the single source of truth for "what does full access mean".

## Base permissions

Named permission groups seeded on install, each with a default bit combination:

```php
'base_permissions' => [
    'leads'      => ['view', 'create', 'update', 'delete'],
    'deals'      => ['view', 'create', 'update'],
    'reports'    => ['view', 'export'],
],
```

## Base roles

```php
'base_roles' => [
    'super_admin' => 'Super Admin',
    'manager'     => 'Manager',
    'agent'       => 'Agent',
],
```

## Role permissions

Maps each base role to the permissions (and bits) it starts with:

```php
'role_permissions' => [
    'manager' => [
        'leads' => ['view', 'create', 'update', 'delete'],
        'deals' => ['view', 'create', 'update'],
    ],
    'agent' => [
        'leads' => ['view', 'create'],
        'deals' => ['view'],
    ],
],
```

## Super admin role

```php
'super_admin_role' => 'super_admin',
```

Whichever role matches this key gets `isSuperAdmin()` to return `true` and bypasses the database entirely in the middleware — `setAccess()` is called with the sum of every positive bit, computed from config in memory.

## Base routes

Named route wildcards registered on seed, used to match middleware checks:

```php
'base_routes' => [
    'leads.*'   => 'web',
    'deals.*'   => 'web',
    'reports.*' => 'web',
],
```

Rather than maintaining this list by hand, run:

```bash
php artisan bwp:sync-routes
```

It scans every named route in the project, converts each name to its wildcard (`leads.index` → `leads.*`), and lets you confirm which ones to register. Use `--dry-run` to preview without saving.

## Base menus

Seeded navigation entries, matched against permissions for visibility:

```php
'base_menus' => [
    [
        'key'   => 'leads',
        'label' => 'Leads',
        'icon'  => 'users',
        'route' => 'leads.index',
    ],
],
```

See [Menus](menus.md) for the full tree structure, including nested items.

## Admin UI gate

Controls who can reach the bundled Livewire UI at `/bwp/*`:

```php
'gate' => function ($user) {
    return $user->hasRole('super_admin');
},
```

Evaluated by `CheckBwpUiGateMiddleware` against the `bwp-ui` gate on every request to a `/bwp/*` route. Denying it aborts with a `403`.

## Middleware alias

```php
'middleware' => [
    'alias' => 'bwp.permission',
],
```

Rename this if `bwp.permission` collides with something else in your app.
