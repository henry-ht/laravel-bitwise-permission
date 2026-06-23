<?php

return [

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
    'bits' => [
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
    ],

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
            'icon'        => null
        ],
        [
            'name'        => 'password.*',
            'type'        => 'web',
            'patch'       => '/password',
            'description' => 'Cambio de contraseña',
            'icon'        => null
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
    | UI — Vistas Livewire
    |--------------------------------------------------------------------------
    | Controla qué vistas del paquete están disponibles.
    | Si publicas las vistas con vendor:publish puedes personalizarlas.
    */
    'ui' => [
        'enabled'    => true,
        'route_prefix' => 'bwp',         // acceso en: /bwp/roles, /bwp/accesses...
        'middleware' => ['web', 'auth'],  // middleware que protege las rutas UI
    ],

];
