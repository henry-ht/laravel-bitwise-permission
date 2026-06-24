<?php

$bits = [
        'view'          => 1,
        'view_any'      => 2,
        'create'        => 4,
        'update'        => 8,
        'delete'        => 16,
        'restore'       => 32,
        'force_delete'  => 64,
        'change_status' => 128,
        'assign'        => 256,
        'support'       => 512,

        // Extensiones personalizadas (ejemplo):
        // 'export'     => 1024,
        // 'import'     => 2048,
        // 'approve'    => 4096,
    ];

return [

    /*
    |--------------------------------------------------------------------------
    | Dashboard Route
    |--------------------------------------------------------------------------
    |
    | Ruta a la que se redirigirá el usuario cuando salga del módulo
    | de permisos.
    |
    */

    // 'dashboard_route' => env('BWP_DASHBOARD_ROUTE', '/'),
    // 'dashboard_route' => "dashboard",

    /*
    |--------------------------------------------------------------------------
    | Gate de acceso a la UI del paquete
    |--------------------------------------------------------------------------
    | Callback que recibe el usuario autenticado y retorna bool.
    | Define aquí quién puede ver /bwp/roles, /bwp/accesses, etc.
    |
    | Ejemplos:
    |   Con el trait HasPermissionsTrait:
    |     fn($user) => $user->canViewAny('bwp.*')
    |
    |   Por rol:
    |     fn($user) => in_array($user->role->name, ['super_admin', 'admin'])
    |
    |   Por campo en users:
    |     fn($user) => $user->is_admin === true
    |
    |   Solo super_admin (recomendado en producción):
    |     fn($user) => $user->role?->name === 'super_admin'
    |
    | Por defecto (null) → cualquier usuario autenticado puede entrar.
    */
    // 'gate' => function ($user) {
    //     // return $user->canViewAny('bwp.*'); // ejemplo con el trait
    //     // return $user->hasRole('super_admin');
    //     // return $user->is_admin;
    // },
    // 'gate' => fn($user) => $user->role?->name === 'super_admin',

    /*
    |--------------------------------------------------------------------------
    | Tabla prefix
    |--------------------------------------------------------------------------
    | Prefijo para todas las tablas del paquete.
    | Por defecto: 'bwp_'
    | Ejemplo: bwp_roles, bwp_permissions, bwp_accesses...
    */
    'table_prefix' => 'bwp_',

    /*
    |--------------------------------------------------------------------------
    | Modelo de usuario
    |--------------------------------------------------------------------------
    | Modelo que usa el trait HasPermissionsTrait.
    */
    'user_model' => \App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Bits de permisos
    |--------------------------------------------------------------------------
    | Puedes extender añadiendo nuevos bits.
    | IMPORTANTE: usa potencias de 2 para no colisionar.
    | Los bits del 1 al 512 están reservados por el paquete.
    | Para extender empieza desde 1024.
    |
    | Regla base: sin 'view' (1) ningún otro bit tiene efecto.
    */
    'bits' => $bits,

    /*
    |--------------------------------------------------------------------------
    | Permisos base del sistema
    |--------------------------------------------------------------------------
    | Rutas que todo proyecto tiene. Se registran automáticamente
    | al correr el seeder o el comando bwp:install.
    | Puedes agregar más según tu proyecto.
    */
    'base_routes' => [
        [
            'name'        => 'profile.*',
            'type'        => 'web',
            'patch'       => '/profile',
            'description' => 'Perfil de usuario',
        ],
        [
            'name'        => 'password.*',
            'type'        => 'web',
            'patch'       => '/password',
            'description' => 'Cambio de contraseña',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Roles base
    |--------------------------------------------------------------------------
    | Roles que se crean por defecto al instalar el paquete.
    | Puedes modificarlos o agregar los tuyos.
    */
    'base_roles' => [
        [
            'name'        => 'super_admin',
            'public_name' => 'Super Administrador',
            'description' => 'Acceso total al sistema',
            'is_base_role'=> true,
        ],
        [
            'name'        => 'admin',
            'public_name' => 'Administrador',
            'description' => 'Administrador del sistema',
            'is_base_role'=> true,
        ],
        [
            'name'        => 'user',
            'public_name' => 'Usuario',
            'description' => 'Usuario estándar',
            'is_base_role'=> true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permisos por rol
    |--------------------------------------------------------------------------
    | Define qué acceso tiene cada rol sobre cada ruta wildcard.
    | El valor es la suma de los bits que quieres otorgar.
    |
    | Ejemplo:
    |   view(1) + view_any(2) + create(4) + update(8) + delete(16) = 31
    |
    | Puedes usar los helpers:
    |   \HenryHt\BitwisePermission\Helpers\BitwiseHelper::combine(['view','create','update'])
    */
    'role_permissions' => [

        'super_admin' => [
            // Super admin tiene acceso total a todo (1023 = todos los bits)
            '*' => 1 | 2 | 4 | 8 | 16 | 32 | 64 | 128 | 256 | 512,
        ],

        'admin' => [
            'profile.*'  => 1 | 2,       // view + view_any
            'password.*' => 1 | 8,       // view + update
            // Agrega más rutas según tu proyecto:
            // 'users.*'    => 1 | 2 | 4 | 8 | 16,
        ],

        'user' => [
            'profile.*'  => 1,           // solo view
            'password.*' => 1 | 8,       // view + update
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    | Nombre del middleware para registrar en las rutas.
    */
    'middleware' => [
        'alias' => 'bwp.permission',
    ],

    /*
    |--------------------------------------------------------------------------
    | Menús base
    |--------------------------------------------------------------------------
    | Menús que se crean al correr el seeder.
    | 'roles' define qué roles base los ven habilitados (null = todos).
    | 'father_name' define el slug del padre si es submenú.
    */
    'base_menus' => [
        [
            'name'        => 'dashboard',
            'public_name' => 'Dashboard',
            'patch'       => 'dashboard',
            'icon'        => 'fa-solid fa-house',
            'order'       => 1,
            'roles'       => ['super_admin', 'admin', 'user'],
        ],
        [
            'name'        => 'profile',
            'public_name' => 'Mi perfil',
            'patch'       => 'profile.edit',
            'icon'        => 'fa-regular fa-user',
            'order'       => 99,
            'roles'       => ['super_admin', 'admin', 'user'],
        ],
        // Agrega aquí los menús de tu proyecto:
        // [
        //     'name'        => 'leads',
        //     'public_name' => 'Leads',
        //     'patch'       => 'leads.index',
        //     'icon'        => 'fa-solid fa-users-line',
        //     'order'       => 2,
        //     'roles'       => ['super_admin', 'admin'],
        //     'children'    => []
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | UI — Vistas Livewire
    |--------------------------------------------------------------------------
    | Controla qué vistas del paquete están disponibles.
    | Si publicas las vistas con vendor:publish puedes personalizarlas.
    */
    'ui' => [
        'enabled'    => true,
        'route_prefix' => 'bwp',         // acceso en: /bwp/roles, /bwp/accesses...
        'middleware' => ['web', 'auth'],  // middleware que protege las rutas UI 'bwp.ui'
    ],

];
