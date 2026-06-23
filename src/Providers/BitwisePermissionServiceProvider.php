<?php

namespace HenryHt\BitwisePermission\Providers;

use HenryHt\BitwisePermission\Console\Commands\InstallCommand;
use HenryHt\BitwisePermission\Console\Commands\SyncRoutesCommand;
use HenryHt\BitwisePermission\Http\Livewire\Accesses\AccessFormComponent;
use HenryHt\BitwisePermission\Http\Livewire\Accesses\AccessTableComponent;
use HenryHt\BitwisePermission\Http\Livewire\Menus\MenuFormComponent;
use HenryHt\BitwisePermission\Http\Livewire\Menus\MenuRoleComponent;
use HenryHt\BitwisePermission\Http\Livewire\Menus\MenuTableComponent;
use HenryHt\BitwisePermission\Http\Livewire\Permissions\PermissionTableComponent;
use HenryHt\BitwisePermission\Http\Livewire\Roles\RoleFormComponent;
use HenryHt\BitwisePermission\Http\Livewire\Roles\RoleTableComponent;
use HenryHt\BitwisePermission\Http\Livewire\Routes\RouteFormComponent;
use HenryHt\BitwisePermission\Http\Livewire\Routes\RouteTableComponent;
use HenryHt\BitwisePermission\Middleware\CheckPermissionMiddleware;
use HenryHt\BitwisePermission\Models\AppRoute;
use HenryHt\BitwisePermission\Models\Menu;
use HenryHt\BitwisePermission\Models\Role;
use HenryHt\BitwisePermission\Observers\AppRouteObserver;
use HenryHt\BitwisePermission\Observers\MenuObserver;
use HenryHt\BitwisePermission\Observers\RoleObserver;
use HenryHt\BitwisePermission\Routes\BitwisePermissionRoutes;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class BitwisePermissionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/bitwise-permission.php',
            'bitwise-permission'
        );

        $this->loadViewsFrom(
            __DIR__ . '/../../resources/views',
            'bwp'
        );
    }

    public function boot(): void
    {
        $this->registerPublishables();
        $this->registerMigrations();
        $this->registerViews();
        $this->registerCommands();
        $this->registerMiddleware();
        $this->registerObservers();
        $this->registerLivewireComponents();
        $this->registerRoutes();
    }

    // ─────────────────────────────────────────────────────────
    // Middleware — alias automático
    // ─────────────────────────────────────────────────────────

    protected function registerMiddleware(): void
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $alias  = config('bitwise-permission.middleware.alias', 'bwp.permission');

        $router->aliasMiddleware($alias, CheckPermissionMiddleware::class);
    }

    // ─────────────────────────────────────────────────────────
    // Observers — relaciones automáticas
    // ─────────────────────────────────────────────────────────

    protected function registerObservers(): void
    {
        Role::observe(RoleObserver::class);
        AppRoute::observe(AppRouteObserver::class);
        Menu::observe(MenuObserver::class);
    }

    // ─────────────────────────────────────────────────────────
    // Publishables
    // ─────────────────────────────────────────────────────────

    protected function registerPublishables(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/bitwise-permission.php' => config_path('bitwise-permission.php'),
        ], 'bwp-config');

        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'bwp-migrations');

        $this->publishes([
            __DIR__ . '/../../database/seeders/' => database_path('seeders'),
        ], 'bwp-seeders');

        $this->publishes([
            __DIR__ . '/../../resources/views/' => resource_path('views/vendor/bitwise-permission'),
        ], 'bwp-views');

        $this->publishes([
            __DIR__ . '/../../resources/css/' => public_path('vendor/bitwise-permission'),
        ], 'bwp-assets');

        $this->publishes([
            __DIR__ . '/../../config/bitwise-permission.php'  => config_path('bitwise-permission.php'),
            __DIR__ . '/../../database/migrations/'           => database_path('migrations'),
            __DIR__ . '/../../database/seeders/'              => database_path('seeders'),
            __DIR__ . '/../../resources/views/'               => resource_path('views/vendor/bitwise-permission'),
            __DIR__ . '/../../resources/css/'                 => public_path('vendor/bitwise-permission'),
        ], 'bwp-all');
    }

    protected function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'bwp');
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                SyncRoutesCommand::class,
            ]);
        }
    }

    protected function registerLivewireComponents(): void
    {
        if (! class_exists(\Livewire\Livewire::class)) {
            return;
        }
        Livewire::component('bwp-roles-table', RoleTableComponent::class);
        Livewire::component('bwp-roles-form', RoleFormComponent::class);

        Livewire::component('bwp-permissions-table', PermissionTableComponent::class);

        Livewire::component('bwp-routes-table', RouteTableComponent::class);
        Livewire::component('bwp-routes-form', RouteFormComponent::class);

        Livewire::component('bwp-accesses-table', AccessTableComponent::class);
        Livewire::component('bwp-accesses-form', AccessFormComponent::class);

        Livewire::component('bwp-menus-table', MenuTableComponent::class);
        Livewire::component('bwp-menus-form', MenuFormComponent::class);
        Livewire::component('bwp-menus-role',        MenuRoleComponent::class);
    }

    protected function registerRoutes(): void
    {
        if (config('bitwise-permission.ui.enabled', true)) {
            BitwisePermissionRoutes::register();
        }
    }
}