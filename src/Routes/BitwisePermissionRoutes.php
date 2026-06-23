<?php

namespace HenryHt\BitwisePermission\Routes;

use Illuminate\Support\Facades\Route;

class BitwisePermissionRoutes
{
    /**
     * Registra las rutas de la UI del paquete.
     * Se llama desde el ServiceProvider si ui.enabled = true.
     *
     * Uso manual en routes/web.php:
     *   \HenryHt\BitwisePermission\BitwisePermissionRoutes::register();
     */
    public static function register(): void
    {
        $config = config('bitwise-permission.ui', []);

        if (! ($config['enabled'] ?? true)) {
            return;
        }

        $prefix     = $config['route_prefix'] ?? 'bwp';
        $middleware = $config['middleware']    ?? ['web', 'auth'];

        Route::prefix($prefix)
            ->middleware($middleware)
            ->name('bwp.')
            ->group(function () {

                // Roles
                Route::get('/roles',              fn() => view('bwp::pages.roles.index'))->name('roles.index');
                Route::get('/roles/create',       fn() => view('bwp::pages.roles.create'))->name('roles.create');
                Route::get('/roles/{role}/edit',  fn() => view('bwp::pages.roles.edit', ['roleId' => request()->route('role')]))->name('roles.edit');

                // Permissions (solo lectura — se generan automáticamente)
                Route::get('/permissions',        fn() => view('bwp::pages.permissions.index'))->name('permissions.index');

                // App Routes
                Route::get('/routes',             fn() => view('bwp::pages.routes.index'))->name('routes.index');
                Route::get('/routes/create',      fn() => view('bwp::pages.routes.create'))->name('routes.create');
                Route::get('/routes/{route}/edit',fn() => view('bwp::pages.routes.edit', ['routeId' => request()->route('route')]))->name('routes.edit');

                // Accesses
                Route::get('/accesses',             fn() => view('bwp::pages.accesses.index'))->name('accesses.index');
                Route::get('/accesses/create',      fn() => view('bwp::pages.accesses.create'))->name('accesses.create');
                Route::get('/accesses/{access}/edit',fn()=> view('bwp::pages.accesses.edit', ['accessId' => request()->route('access')]))->name('accesses.edit');

                // Menus
                Route::get('/menus',              fn() => view('bwp::pages.menus.index'))->name('menus.index');
                Route::get('/menus/create',       fn() => view('bwp::pages.menus.create'))->name('menus.create');
                Route::get('/menus/{menu}/edit',  fn() => view('bwp::pages.menus.edit', ['menuId' => request()->route('menu')]))->name('menus.edit');

            });
    }
}
