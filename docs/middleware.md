# Middleware

## `bwp.permission`

Registered automatically as `Route::middleware('bwp.permission')`. It resolves access for the current route, then checks bits against it.

```php
Route::middleware('bwp.permission')->group(function () {
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
});

Route::middleware('bwp.permission:create')->group(function () {
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
});

Route::middleware('bwp.permission:delete')->group(function () {
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
});
```

### What happens on every request

1. If the user isn't authenticated, redirect to `login`.
2. If the current route has no name, the request passes through untouched — the package can only resolve access for named routes.
3. If the user `isSuperAdmin()`, access is set to the sum of every positive bit in config and the request proceeds — no query.
4. Otherwise, `$user->resolveAccess($routeName)` looks up the matching wildcard permission and returns the access integer, which is cached on the user via `setAccess()` for the rest of the request (available in controllers and views).
5. The **`view`** bit is checked first, unconditionally. If it isn't present, the request is denied — this happens even if a middleware parameter like `create` was satisfied.
6. If a bit parameter was passed (e.g. `create`), that bit is checked too.

### The `view` prerequisite rule

This is the one rule to remember: **no bit works without `view`.** A user with `create + update` but without `view` is still denied, because `view` represents "is allowed to be here at all," and everything else is scoped inside that.

```php
// bits: view=1, create=4  → access = 4 (create only, no view)
$access & 1 === 1   // false — denied, regardless of the create bit
```

This mirrors how the config is meant to be used: always include `view` in every permission that should be reachable at all.

### Denied requests

```php
if ($request->expectsJson()) {
    return response()->json(['message' => 'Unauthorized.'], 403);
}

abort(403);
```

## `bwp-ui` gate middleware

Protects every route under `/bwp/*` — the bundled admin UI. It evaluates the `bwp-ui` gate, which you define in the `gate` config key (see [Configuration](configuration.md)):

```php
'gate' => function ($user) {
    return $user->hasRole('super_admin');
},
```

Unauthenticated users are redirected to `login`; authenticated users who fail the gate get a `403`.

## Combining with other middleware

The permission middleware only checks authorization — pair it with `auth` (or your guard of choice) as usual:

```php
Route::middleware(['auth', 'bwp.permission:update'])->group(function () {
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
});
```
