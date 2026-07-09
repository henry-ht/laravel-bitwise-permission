# Examples

## Creating a user with a cloned role

The common flow: a new user is created and immediately needs its own role, so that permission edits later never touch the shared base role.

```php
use HenryHt\BitwisePermission\Services\RoleCloneService;
use HenryHt\BitwisePermission\Models\Role;

class RegisterUserAction
{
    public function __construct(protected RoleCloneService $roleClone) {}

    public function handle(array $data): User
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $baseRole = Role::where('name', 'agent')->firstOrFail();

        $this->roleClone->cloneForUser($user, $baseRole);

        return $user;
    }
}
```

## Protecting a resource controller

```php
Route::middleware('bwp.permission')->group(function () {
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
});

Route::middleware('bwp.permission:create')->group(function () {
    Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
});

Route::middleware('bwp.permission:update')->group(function () {
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
});

Route::middleware('bwp.permission:delete')->group(function () {
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
});
```

Run `php artisan bwp:sync-routes` after adding these so the wildcards (`leads.*`) are registered and available to assign in `/bwp/accesses`.

## Conditional UI

```blade
<div class="toolbar">
    @if(auth()->user()->canCreate())
        <a href="{{ route('leads.create') }}" class="btn">New lead</a>
    @endif

    @if(auth()->user()->canDelete())
        <button wire:click="delete({{ $lead->id }})">Delete</button>
    @endif
</div>

@if(auth()->user()->isSuperAdmin())
    <span class="badge">Super Admin</span>
@endif
```

## Adding a custom bit

Add it to `bits` alongside the existing ones, as the next power of two:

```php
'bits' => [
    'view'          => 1,
    'export'        => 2,
    'create'        => 4,
    'update'        => 8,
    'delete'        => 16,
    'modify_access' => 32,
    'approve'       => 64, // new
],
```

Then check it with `canCustom()`:

```php
if ($user->canCustom('approve', 'expenses.approve')) {
    // ...
}
```

## Adjusting a single user's permission

```php
$user->setPermission('reports.*', 'view + export');
```

This only ever touches the `Access` row for the user's own (cloned) role — the `manager` base role that user started from is untouched, so every other manager keeps the original permission set.

## Building a permission-management form

```php
$route  = 'leads.*';
$current = $user->getPermissionFor($route); // ?Permission, for pre-selecting a value

// on submit
$user->setPermission($route, $request->input('permission'));
```

For editing several routes in one screen:

```php
$all = $user->getAllPermissions(); // route_name => Permission

// on submit
$results = $user->setPermissions($request->input('permissions'));
```

## The admin UI

With the bundled Livewire components (published automatically, gated by the `gate` config key), you get:

| Route | Purpose |
|---|---|
| `/bwp/roles` | Create, rename and inspect roles |
| `/bwp/permissions` | Manage named permission groups and their bit combinations |
| `/bwp/routes` | Review and edit registered route wildcards |
| `/bwp/accesses` | Assign a permission to a role for a given route |
| `/bwp/menus` | Build and reorder the navigation tree |
| `/bwp/menus/roles` | Toggle menu visibility per role |

Restrict access with the `gate` closure in `config/bitwise-permission.php`:

```php
'gate' => fn ($user) => $user->hasRole('super_admin'),
```
