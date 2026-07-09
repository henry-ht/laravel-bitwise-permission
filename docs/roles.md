# Roles

## Base roles vs. per-user roles

A **base role** (`is_base_role = true`) is a template — `manager`, `agent`, `super_admin` — defined in config and seeded once. Base roles are never deleted and never assigned raw permission edits meant for a single person.

A **per-user role** is a private clone of a base role, created the moment a user needs permissions that can diverge from the template. This is what keeps "give Alice one extra permission" from mutating everyone else who shares the `agent` role.

## Cloning a role for a new user

```php
use HenryHt\BitwisePermission\Services\RoleCloneService;
use HenryHt\BitwisePermission\Models\Role;

$roleClone = app(RoleCloneService::class);

$user = User::create([
    'name'  => 'Alice',
    'email' => 'alice@example.com',
]);

$baseRole = Role::where('name', 'agent')->firstOrFail();

$roleClone->cloneForUser($user, $baseRole);
```

`cloneForUser()`:

1. Creates a new role named `{base_role_name}_{random8}` (e.g. `agent_x7k2p9qz`), with `base_role_id` pointing back to the template.
2. Copies every `Access` row (route × permission) from the base role to the new one.
3. Copies every menu-visibility relation from the base role to the new one.
4. Assigns the new role to the user (`role_id`).

From that point on, `$roleClone->deleteRoleForUser($user)` or editing `$user`'s permissions directly (see [Permissions](permissions.md)) only ever touches Alice's own role — `agent` itself is untouched.

## Cloning without assigning

Useful for preparing a variant role ahead of time:

```php
$variant = $roleClone->cloneRole($baseRole, suffix: 'senior');
// creates "agent_senior"
```

Omit `$suffix` to get a random 8-character suffix instead.

## Deleting a user's role

```php
$roleClone->deleteRoleForUser($user);
```

This removes the role's accesses, its menu relations, and the role itself, and detaches it from the user first to avoid an orphaned foreign key. **Base roles are always protected** — if the user's role has `is_base_role = true`, the method does nothing and returns `false`.

```php
$roleClone->deleteRoleById($roleId);
```

Same protection, but by ID — also detaches any user still pointing at that role before deleting it.

## Checking a role

```php
$user->hasRole('super_admin');
$user->isSuperAdmin(); // shortcut for the configured super_admin_role
```

`isSuperAdmin()` is what the [middleware](middleware.md) checks before touching the database at all — see [Configuration](configuration.md#super-admin-role) for how it's wired up.

## Model reference

```php
class Role extends Model
{
    // fillable: name, public_name, description, is_base_role, base_role_id
    public function accesses(): HasMany;
    public function menus(): BelongsToMany;
}
```

Creating or updating a `Role` fires `RoleObserver`, which provisions a "no access" `Access` row for every registered route and a disabled entry for every menu — so a brand-new role starts fully locked down until permissions are explicitly granted.
