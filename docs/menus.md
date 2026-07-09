# Menus

## How menu visibility works

Menus are a separate concern from route permissions — a role can be allowed to `view` a route but still have its menu entry hidden, and vice versa isn't enforced automatically, so pair both when you want a fully hidden feature.

Each `Menu` belongs to a role through the `bwp_menu_role` pivot, with a `disabled` flag. A brand-new role starts with every menu item disabled — `RoleObserver` provisions that row automatically, the same way it provisions "no access" for routes (see [Roles](roles.md)).

## Defining the base tree

```php
'base_menus' => [
    [
        'key'   => 'leads',
        'label' => 'Leads',
        'icon'  => 'users',
        'route' => 'leads.index',
    ],
    [
        'key'    => 'leads-create',
        'label'  => 'New lead',
        'icon'   => 'plus',
        'route'  => 'leads.create',
        'father' => 'leads',
    ],
],
```

Use `father` to nest an item under a parent's `key`. The seeder resolves these into `father_id` foreign keys on `bwp_menus`.

## Enabling and disabling for a user's role

```php
$user->setMenuAccess('leads', true);
$user->setMenuAccess('leads', false);
$user->setMenuAccess(3, true); // by menu ID instead of key
```

### Parent propagation

This is the detail that makes menu management pleasant instead of fiddly: **enabling or disabling a child automatically syncs its parent.**

- Enabling a child → its parent is enabled too, so the tree never shows an orphaned child under a hidden parent.
- Disabling a child → the parent is only disabled if **no other children** of that parent are still enabled.

```php
$user->setMenuAccess('leads-create', true);
// → 'leads-create' enabled, 'leads' (its parent) enabled automatically

$user->setMenuAccess('leads-create', false);
// → 'leads-create' disabled; 'leads' stays enabled only if it has
//   another enabled child, otherwise it's disabled too
```

## Setting multiple items at once

```php
$user->setMenuAccesses([
    'leads'        => true,
    'leads-create' => true,
    'deals'        => false,
]);
```

## Checking and reading

```php
$user->hasMenuAccess('leads');   // bool
$user->hasMenuAccess(3);          // by ID

$menu = $user->getMenu();          // Collection of enabled Menu items for this user's role
```

`getMenu()` is what you render navigation from — it already reflects role-based visibility, so the view layer doesn't need to know about permissions at all:

```blade
@foreach ($user->getMenu() as $item)
    <a href="{{ route($item->route) }}">
        <i class="{{ $item->icon }}"></i> {{ $item->label }}
    </a>
@endforeach
```

## Managing menus visually

The bundled admin UI exposes this at `/bwp/menus` (editing the tree itself) and `/bwp/menus/roles` (toggling visibility per role) — see [Examples](examples.md) for a walk-through.
