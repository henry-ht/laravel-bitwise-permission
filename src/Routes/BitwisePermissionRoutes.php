<?php

namespace HenryHt\BitwisePermission\Routes;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class BitwisePermissionRoutes
{
    public static function register(): void
    {
        $config = config('bitwise-permission.ui', []);

        if (! ($config['enabled'] ?? true)) {
            return;
        }

        $prefix     = $config['route_prefix'] ?? 'bwp';
        $middleware = $config['middleware']    ?? ['web', 'auth'];

        // Registrar el gate si está definido en el config
        static::registerGate();

        Route::prefix($prefix)
            ->middleware($middleware)
            ->name('bwp.')
            ->group(function () {

                // Roles
                Route::get('/roles',             fn() => view('bwp::pages.roles.index'))
                    ->name('roles.index');
                Route::get('/roles/create',      fn() => view('bwp::pages.roles.create'))
                    ->name('roles.create');
                Route::get('/roles/{role}/edit', fn($role) => view('bwp::pages.roles.edit', ['roleId' => $role]))
                    ->name('roles.edit');

                // Permissions
                Route::get('/permissions', fn() => view('bwp::pages.permissions.index'))
                    ->name('permissions.index');

                // Routes
                Route::get('/routes',              fn() => view('bwp::pages.routes.index'))
                    ->name('routes.index');
                Route::get('/routes/create',       fn() => view('bwp::pages.routes.create'))
                    ->name('routes.create');
                Route::get('/routes/{route}/edit', fn($route) => view('bwp::pages.routes.edit', ['routeId' => $route]))
                    ->name('routes.edit');

                // Accesses
                Route::get('/accesses',               fn() => view('bwp::pages.accesses.index'))
                    ->name('accesses.index');
                Route::get('/accesses/create',        fn() => view('bwp::pages.accesses.create'))
                    ->name('accesses.create');
                Route::get('/accesses/{access}/edit', fn($access) => view('bwp::pages.accesses.edit', ['accessId' => $access]))
                    ->name('accesses.edit');

                // Menus
                Route::get('/menus',             fn() => view('bwp::pages.menus.index'))
                    ->name('menus.index');
                Route::get('/menus/create',      fn() => view('bwp::pages.menus.create'))
                    ->name('menus.create');
                Route::get('/menus/{menu}/edit', fn($menu) => view('bwp::pages.menus.edit', ['menuId' => $menu]))
                    ->name('menus.edit');
                Route::get('/menus/roles',       fn() => view('bwp::pages.menus.roles'))
                    ->name('menus.roles');

            });
    }

    /**
     * Registra el gate 'bwp-ui' usando el callback del config.
     * Si no hay callback definido, solo verifica que el usuario esté autenticado.
     */
    protected static function registerGate(): void
    {
        Gate::define('bwp-ui', function ($user) {
            $callback = config('bitwise-permission.gate');

            if (is_callable($callback)) {
                return $callback($user);
            }

            // Por defecto — solo autenticado
            return false;
        });
    }
}