<?php

$bits = [
    'no_access'     => 0,
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
];

$permissions = [
    // Sin acceso
    'no access' => $bits['no_access'],

    // Individuales
    'view' => $bits['view'],
    'view any' => $bits['view_any'],
    'create' => $bits['create'],
    'update' => $bits['update'],
    'delete' => $bits['delete'],
    'restore' => $bits['restore'],
    'force delete' => $bits['force_delete'],
    'change status' => $bits['change_status'],
    'assign' => $bits['assign'],
    'support' => $bits['support'],

    'read access' => $bits['view']
        | $bits['view_any'],

    'edit access' => $bits['view']
        | $bits['update'],

    'create access' => $bits['view']
        | $bits['create'],

    'create and edit access' => $bits['view']
        | $bits['create']
        | $bits['update'],

    'delete access' => $bits['view']
        | $bits['delete'],

    'restore access' => $bits['view']
        | $bits['restore'],

    'force delete access' => $bits['view']
        | $bits['force_delete'],

    'status management access' => $bits['view']
        | $bits['change_status'],

    'assignment access' => $bits['view']
        | $bits['assign'],

    'support access' => $bits['view']
        | $bits['support'],

    // Combinaciones avanzadas
    'write access' => $bits['view']
        | $bits['view_any']
        | $bits['create']
        | $bits['update'],

    'modify access' => $bits['view']
        | $bits['view_any']
        | $bits['create']
        | $bits['update']
        | $bits['delete'],

    'modify access with restore' => $bits['view']
        | $bits['view_any']
        | $bits['create']
        | $bits['update']
        | $bits['delete']
        | $bits['restore'],

    'modify access with force delete' => $bits['view']
        | $bits['view_any']
        | $bits['create']
        | $bits['update']
        | $bits['delete']
        | $bits['restore']
        | $bits['force_delete'],

    'modify access with status management' => $bits['view']
        | $bits['view_any']
        | $bits['create']
        | $bits['update']
        | $bits['delete']
        | $bits['change_status'],

    'modify access with assignment' => $bits['view']
        | $bits['view_any']
        | $bits['create']
        | $bits['update']
        | $bits['delete']
        | $bits['assign'],

    'modify access with support' => $bits['view']
        | $bits['view_any']
        | $bits['create']
        | $bits['update']
        | $bits['delete']
        | $bits['support'],

    'full management access' => $bits['view']
        | $bits['view_any']
        | $bits['create']
        | $bits['update']
        | $bits['delete']
        | $bits['restore']
        | $bits['force_delete']
        | $bits['change_status']
        | $bits['assign'],

    'full access' => $bits['view']
        | $bits['view_any']
        | $bits['create']
        | $bits['update']
        | $bits['delete']
        | $bits['restore']
        | $bits['force_delete']
        | $bits['change_status']
        | $bits['assign']
        | $bits['support'],
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

    'base_permissions' => $permissions,
    
    /*
    |--------------------------------------------------------------------------
    | Super Admin Role
    |--------------------------------------------------------------------------
    | Nombre del rol que tiene acceso total al sistema.
    | El middleware y el trait retornan acceso total directamente
    | sin consultar la base de datos para este rol.
    |
    | Esto garantiza que el super admin nunca quede bloqueado
    | aunque no tenga registros en bwp_accesses.
    |
    | Puedes cambiarlo al nombre que uses en tu proyecto:
    |   'super_admin_role' => 'root',
    |   'super_admin_role' => 'god',
    |   'super_admin_role' => 'owner',
    */
    'super_admin_role' => 'super_admin',

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
            'is_base_role' => true,
        ],
        [
            'name'        => 'admin',
            'public_name' => 'Administrador',
            'description' => 'Administrador del sistema',
            'is_base_role' => true,
        ],
        [
            'name'        => 'user',
            'public_name' => 'Usuario',
            'description' => 'Usuario estándar',
            'is_base_role' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permisos por rol
    |--------------------------------------------------------------------------
    | Define qué acceso tiene cada rol sobre cada ruta wildcard.
    */
    'role_permissions' => [
        'super_admin' => [
            '*' => 'full access',
        ],

        'admin' => [
            'profile.*'  => 'read access',
            'password.*' => 'edit access',

            // Ejemplos:
            // 'users.*' => 'modify access',
            // 'roles.*' => 'full management access',
            // 'menus.*' => ['view', 'view any', 'create', 'update'],
        ],

        'user' => [
            'profile.*'  => 'view',
            'password.*' => 'edit access',
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
