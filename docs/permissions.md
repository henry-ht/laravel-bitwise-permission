# Permissions

## Checking permissions

`HasPermissionsTrait` exposes one method per common bit. Each accepts an optional route name — omit it to check against the current route.

```php
$user->canView();
$user->canViewAny();
$user->canCreate();
$user->canUpdate();
$user->canDelete();
$user->canRestore();
$user->canForceDelete();
$user->canChangeStatus();
$user->canAssign();
$user->canSupport();

// against a specific route rather than the current one
$user->canUpdate('leads.update');
```

For a bit that isn't one of the named helpers, use `canCustom()`:

```php
$user->canCustom('export');
$user->canCustom('modify_access', 'leads.update');
```

Every one of these ultimately compares the resolved access integer against the configured bit — see [Middleware](middleware.md) for the resolution and the `view` prerequisite rule that applies here too.

## Reading and caching access

```php
$user->getAccess();          // int — the currently cached access value
$user->setAccess(13);        // set it manually (used internally by the middleware)
$user->clearAccessCache();   // force re-resolution on the next check
```

Access is resolved once per route per request and cached in memory on the user instance (`bwpAccessCache`), so calling `canView()` and `canUpdate()` back to back doesn't hit the database twice.

## Setting a permission

```php
// by permission name, as defined in base_permissions
$user->setPermission('leads.*', 'modify access');

// by raw bitwise value
$user->setPermission('leads.*', 13);
```

`setPermission()` accepts either a route name or a wildcard — it normalizes internally — resolves the matching `Permission`, and creates or updates the `Access` row for the **user's current role** (not the base role it may have been cloned from — see [Roles](roles.md)). It returns `false` if the route or the permission doesn't exist, so you can validate before trusting the result.

## Setting multiple permissions at once

```php
$results = $user->setPermissions([
    'leads.*'    => 'modify access',
    'deals.*'    => 'read access',
    'contacts.*' => 'no access',
]);

// ['leads.*' => true, 'deals.*' => true, 'contacts.*' => true]
```

## Reading current permissions

```php
$permission = $user->getPermissionFor('leads.*'); // ?Permission

$all = $user->getAllPermissions(); // Collection<route_name => Permission>
```

Both are useful for pre-filling a permission-management form — the [admin UI](examples.md#the-admin-ui) uses exactly this pair.

## Combining bits manually

`BitwiseHelper` is the low-level primitive everything above is built on:

```php
use HenryHt\BitwisePermission\Helpers\BitwiseHelper;

BitwiseHelper::combine(['view', 'create', 'update']); // 13
BitwiseHelper::decode(13);                             // ['view', 'create', 'update']
BitwiseHelper::has(13, 'create');                       // true
BitwiseHelper::add(13, 'delete');                        // 29
BitwiseHelper::remove(13, 'update');                      // 5
BitwiseHelper::total();                                    // sum of every configured bit
BitwiseHelper::all();                                        // the full bits map from config
```

Reach for this whenever you need to build or inspect an access integer outside of the user context — seeders, artisan commands, tests.

## `hasTotalAccess()` and role helpers

```php
$user->hasTotalAccess();               // true if access === every configured bit combined
$user->hasTotalAccess('leads.update'); // same, scoped to a specific route

$user->getBaseRole();                  // the base Role this user's role was cloned from, or null
$user->wasClonedFrom('agent');         // true if the base role's name matches
```
