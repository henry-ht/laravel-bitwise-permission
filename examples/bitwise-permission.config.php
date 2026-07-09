<?php

/**
 * Example config/bitwise-permission.php customization.
 *
 * See: https://bitwise.tchenry.com/docs/configuration
 */
return [

    'table_prefix' => 'bwp_',

    'bits' => [
        'view'          => 1,
        'export'        => 2,
        'create'        => 4,
        'update'        => 8,
        'delete'        => 16,
        'modify_access' => 32,
    ],

    'base_permissions' => [
        'leads'   => ['view', 'create', 'update', 'delete'],
        'deals'   => ['view', 'create', 'update'],
        'reports' => ['view', 'export'],
    ],

    'base_roles' => [
        'super_admin' => 'Super Admin',
        'manager'     => 'Manager',
        'agent'       => 'Agent',
    ],

    'role_permissions' => [
        'manager' => [
            'leads' => ['view', 'create', 'update', 'delete'],
            'deals' => ['view', 'create', 'update'],
        ],
        'agent' => [
            'leads' => ['view', 'create'],
            'deals' => ['view'],
        ],
    ],

    'super_admin_role' => 'super_admin',

    'base_routes' => [
        'leads.*'   => 'web',
        'deals.*'   => 'web',
        'reports.*' => 'web',
    ],

    'base_menus' => [
        [
            'key'   => 'leads',
            'label' => 'Leads',
            'icon'  => 'users',
            'route' => 'leads.index',
        ],
        [
            'key'    => 'leads-create',
            'label'  => 'New lead',
            'icon'   => 'plus',
            'route'  => 'leads.create',
            'father' => 'leads',
        ],
    ],

    'gate' => function ($user) {
        return $user->hasRole('super_admin');
    },

    'middleware' => [
        'alias' => 'bwp.permission',
    ],

];
