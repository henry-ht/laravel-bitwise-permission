# laravel-bitwise-permission

Sistema de permisos **bitwise** para Laravel. Roles, permisos, rutas y menús con UI Livewire opcional e instalación en minutos.

[![Laravel](https://img.shields.io/badge/Laravel-11%2B-red)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-blue)](https://livewire.laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-purple)](https://php.net)
[![License](https://img.shields.io/badge/license-TC%20Henry%20Commercial-blue)](#licencia)

---

## ¿Qué es un permiso bitwise?

En lugar de guardar una fila en base de datos por cada acción que un usuario puede hacer, un permiso bitwise es **un solo número entero** que representa múltiples acciones combinadas.

Cada acción es una potencia de 2 (un bit):

| Nombre | Valor | Descripción |
|--------|-------|-------------|
| `no_access` | 0 | Sin acceso |
| `view` | 1 | Ver un recurso propio |
| `view_any` | 2 | Ver el listado completo |
| `create` | 4 | Crear |
| `update` | 8 | Actualizar |
| `delete` | 16 | Eliminar |
| `restore` | 32 | Restaurar (soft delete) |
| `force_delete` | 64 | Eliminar permanentemente |
| `change_status` | 128 | Cambiar estado |
| `assign` | 256 | Asignar a otros usuarios |
| `support` | 512 | Acceso de soporte |

Para combinar permisos, simplemente sumas los valores:

```
view + create + update = 1 + 4 + 8 = 13
```

> **Regla base absoluta:** sin el bit `view (1)` activo, ningún otro bit tiene efecto.
> El usuario no puede entrar a la vista aunque tenga otros bits activos.

---

## Instalación

```bash
composer require henry-ht/laravel-bitwise-permission
```

```bash
php artisan bwp:install
```

### Instalación manual

```bash
php artisan vendor:publish --tag=bwp-config
php artisan vendor:publish --tag=bwp-migrations
php artisan vendor:publish --tag=bwp-assets
php artisan migrate
php artisan db:seed --class="HenryHt\BitwisePermission\Database\Seeders\BitwisePermissionSeeder"
```

### Publicar todo de una vez

```bash
php artisan vendor:publish --tag=bwp-all
```

---

## Configuración en tu modelo User

### 1. Agrega `role_id` a la tabla users

```bash
php artisan make:migration add_role_id_to_users_table --table=users
```

```php
// up()
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('role_id')
          ->nullable()
          ->constrained('bwp_roles')
          ->nullOnDelete();
});

// down()
Schema::table('users', function (Blueprint $table) {
    $table->dropConstrainedForeignId('role_id');
});
```

### 2. Incluye el trait en el modelo User

```php
use HenryHt\BitwisePermission\Traits\HasPermissionsTrait;

class User extends Authenticatable
{
    use HasPermissionsTrait;

    protected $fillable = ['name', 'email', 'password', 'role_id'];
}
```

### 3. El middleware se registra automáticamente

```php
Route::middleware('bwp.permission')->group(function () {
    // rutas protegidas
});
```

Para cambiar el alias:

```php
'middleware' => [
    'alias' => 'bwp.permission',
],
```

---

## Configurar el archivo config

### Bits disponibles

Define los bits al inicio del archivo y úsalos en todo el config.
**No uses `BitwiseHelper::combine()` en el config** — se ejecuta antes de que el paquete esté cargado y siempre retorna `0`.

```php
// config/bitwise-permission.php

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
    // Extensiones desde 1024:
    // 'export'  => 1024,
    // 'approve' => 2048,
];

return [
    'bits' => $bits,
    // ...
];
```

### Permisos base

Define combinaciones con nombres semánticos:

```php
$permissions = [
    'no access'     => $bits['no_access'],
    'view'          => $bits['view'],
    'read access'   => $bits['view'] | $bits['view_any'],
    'edit access'   => $bits['view'] | $bits['update'],
    'create access' => $bits['view'] | $bits['create'],

    'write access'  => $bits['view'] | $bits['view_any']
                     | $bits['create'] | $bits['update'],

    'modify access' => $bits['view'] | $bits['view_any']
                     | $bits['create'] | $bits['update'] | $bits['delete'],

    'full access'   => $bits['view'] | $bits['view_any']
                     | $bits['create'] | $bits['update'] | $bits['delete']
                     | $bits['restore'] | $bits['force_delete']
                     | $bits['change_status'] | $bits['assign'] | $bits['support'],
];

return [
    'bits'             => $bits,
    'base_permissions' => $permissions,
    // ...
];
```

### Super Admin

Define el nombre del rol que tiene acceso total al sistema.
El middleware y el trait retornan acceso total directamente **sin consultar la base de datos** para este rol — garantizando que nunca quede bloqueado.

```php
'super_admin_role' => 'super_admin',
```

Puedes cambiarlo al nombre que uses en tu proyecto:

```php
'super_admin_role' => 'root',
'super_admin_role' => 'owner',
```

Con esto en `role_permissions` ya no necesitas definir nada para el super admin:

```php
'role_permissions' => [
    // super_admin se omite — acceso total por config, no por BD
    'admin' => [
        'leads.*' => 'modify access',
        // ...
    ],
],
```

Si aun así defines permisos para el super admin en `role_permissions`, el seeder los ignorará:

```php
// En el seeder, el rol super_admin_role se salta automáticamente
// → "Rol 'super_admin' es super admin — se omite (acceso total por config)."
```

Para verificar en código:

```php
auth()->user()->isSuperAdmin(); // → true/false
```

### Permisos por rol

Referencia los permisos por **nombre** definido en `base_permissions`.
El `*` aplica ese permiso a **todas las rutas existentes** en `bwp_app_routes`:

```php
'role_permissions' => [

    'admin' => [
        '*'          => 'read access',   // leer todo por defecto
        'leads.*'    => 'modify access', // sobreescribir para leads
        'deals.*'    => 'modify access',
        'contacts.*' => 'write access',
        'profile.*'  => 'read access',
        'password.*' => 'edit access',
    ],

    'user' => [
        'profile.*'  => 'view',
        'password.*' => 'edit access',
    ],

],
```

### Gate de acceso a la UI

Define quién puede acceder a `/bwp/roles`, `/bwp/accesses`, etc.:

```php
'gate' => fn($user) => $user->isSuperAdmin(),

// Por múltiples roles
'gate' => fn($user) => in_array($user->role?->name, ['super_admin', 'admin']),

// Sin restricción (por defecto)
'gate' => null,
```

### Menús base con hijos

```php
'base_menus' => [
    [
        'name'        => 'dashboard',
        'public_name' => 'Dashboard',
        'patch'       => 'home',
        'icon'        => 'fa-solid fa-house',
        'order'       => 1,
        'roles'       => ['super_admin', 'admin', 'user'], // null = todos
    ],
    [
        'name'        => 'leads',
        'public_name' => 'Leads',
        'patch'       => 'leads.index',
        'icon'        => 'fa-solid fa-users-line',
        'order'       => 2,
        'roles'       => ['super_admin', 'admin'],
        'children'    => [
            [
                'name'        => 'leads-create',
                'public_name' => 'Nuevo lead',
                'patch'       => 'leads.create',
                'icon'        => 'fa-solid fa-plus',
                'order'       => 1,
                'roles'       => ['super_admin', 'admin'],
            ],
        ],
    ],
],
```

---

## Uso

### En rutas

```php
Route::middleware('bwp.permission')->group(function () {
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
});

Route::middleware('bwp.permission:create')->group(function () {
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
});

Route::middleware('bwp.permission:update')->group(function () {
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
});

Route::middleware('bwp.permission:delete')->group(function () {
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
});
```

El middleware convierte `leads.index` → `leads.*` automáticamente.

---

### Verificar permisos

```php
$user = auth()->user();

$user->isSuperAdmin();    // ← nuevo
$user->canView();
$user->canViewAny();
$user->canCreate();
$user->canUpdate();
$user->canDelete();
$user->canRestore();
$user->canForceDelete();
$user->canChangeStatus();
$user->canAssign();
$user->canSupport();
$user->hasTotalAccess();

// Para una ruta específica
$user->canCreate('deals.*');
$user->canDelete('contacts.*');
$user->canCustom('export', 'reports.*');
```

---

### En vistas Blade

```blade
@if(auth()->user()->isSuperAdmin())
    <span>Super Admin</span>
@endif

@if(auth()->user()->canCreate())
    <a href="{{ route('leads.create') }}">Nuevo lead</a>
@endif

@if(auth()->user()->canUpdate())
    <a href="{{ route('leads.edit', $lead) }}">Editar</a>
@endif

@if(auth()->user()->canDelete())
    <button>Eliminar</button>
@endif

@if(auth()->user()->canCustom('export'))
    <button>Exportar CSV</button>
@endif
```

---

### En componentes Livewire

```php
public function delete(int $id): void
{
    if (! auth()->user()->canDelete()) {
        $this->dispatch('notify', type: 'error', message: 'Sin permiso.');
        return;
    }
    Lead::findOrFail($id)->delete();
}

public function render()
{
    return view('livewire.leads.table', [
        'leads'        => Lead::paginate(15),
        'isSuperAdmin' => auth()->user()->isSuperAdmin(),
        'canCreate'    => auth()->user()->canCreate(),
        'canUpdate'    => auth()->user()->canUpdate(),
        'canDelete'    => auth()->user()->canDelete(),
    ]);
}
```

---

## Gestión dinámica de permisos

### Cambiar el permiso de una ruta

```php
// Por nombre de permiso (definido en base_permissions)
$user->setPermission('leads.*', 'modify access');

// Por valor numérico directo
$user->setPermission('leads.*', 31);

// También acepta nombres de ruta — convierte a wildcard automáticamente
$user->setPermission('leads.index', 'read access'); // → leads.*

// Retorna true si se guardó, false si la ruta o permiso no existe
$result = $user->setPermission('leads.*', 'modify access');
```

### Cambiar múltiples permisos de una vez

```php
$results = $user->setPermissions([
    'leads.*'    => 'modify access',
    'deals.*'    => 'read access',
    'contacts.*' => 'no access',
    'reports.*'  => 31,
]);
// → ['leads.*' => true, 'deals.*' => true, ...]
```

### Obtener el permiso actual de una ruta

```php
$permission = $user->getPermissionFor('leads.*');
$permission?->name;    // 'modify access'
$permission?->access;  // 31
```

### Obtener todos los permisos del usuario

```php
$permisos = $user->getAllPermissions();
// → Collection ['leads.*' => Permission, 'deals.*' => Permission, ...]

foreach ($permisos as $ruta => $permission) {
    echo "{$ruta}: {$permission->name} ({$permission->access})";
}
```

---

## Gestión dinámica de menú

### Habilitar / deshabilitar un ítem

```php
$user->setMenuAccess('leads', true);
$user->setMenuAccess('leads-create', false);
$user->setMenuAccess(3, true); // por ID
```

**Propagación automática al padre:**
- Habilitar un hijo → habilita el padre automáticamente
- Deshabilitar un hijo → si no quedan otros hijos habilitados, deshabilita el padre
- Recursivo en N niveles

### Múltiples ítems de una vez

```php
$user->setMenuAccesses([
    'leads'         => true,
    'leads-create'  => true,
    'leads-reports' => false,
    'deals'         => true,
]);
```

### Verificar acceso a un ítem

```php
$user->hasMenuAccess('leads');
$user->hasMenuAccess(5);
```

### Obtener el menú del usuario

```php
public function render()
{
    return view('layouts.sidebar', [
        'menuItems' => auth()->user()->getMenu(),
    ]);
}
```

```blade
@foreach($menuItems as $item)
    <a href="{{ route($item->patch) }}">
        <i class="{{ $item->icon }}"></i>
        {{ $item->public_name }}
    </a>
    @foreach($item->childrenOrdered as $child)
        <a href="{{ route($child->patch) }}">
            └ {{ $child->public_name }}
        </a>
    @endforeach
@endforeach
```

---

## RoleCloneService — roles por usuario

Cada usuario tiene su **propio rol único** clonado a partir de un rol base,
permitiendo personalizar permisos individuales sin afectar el rol base.

```php
use HenryHt\BitwisePermission\Services\RoleCloneService;
use HenryHt\BitwisePermission\Models\Role;

$user     = User::create([...]);
$baseRole = Role::where('name', 'user')->firstOrFail();

app(RoleCloneService::class)->cloneForUser($user, $baseRole);
// Crea 'user_a3f9x2k1' con todos los accesses y menús del rol base
```

### Clonar sin asignar

```php
$newRole = app(RoleCloneService::class)->cloneRole($baseRole);
$newRole = app(RoleCloneService::class)->cloneRole($baseRole, 'equipo_ventas');
// → 'user_equipo_ventas'
```

### Flujo completo de creación de usuario

```php
public function store(Request $request, RoleCloneService $roleClone)
{
    $validated = $request->validate([
        'name'      => 'required|string|max:255',
        'email'     => 'required|email|unique:users',
        'password'  => 'required|min:8',
        'base_role' => 'required|string|exists:bwp_roles,name',
    ]);

    $user = User::create([
        'name'     => $validated['name'],
        'email'    => $validated['email'],
        'password' => bcrypt($validated['password']),
    ]);

    $baseRole = Role::where('name', $validated['base_role'])->firstOrFail();
    $roleClone->cloneForUser($user, $baseRole);

    // Personalizar permisos del usuario si es necesario
    $user->setPermission('reports.*', 'read access');
    $user->setMenuAccess('reports', true);

    return redirect()->route('users.index')->with('success', 'Usuario creado.');
}
```

---

## BitwiseHelper

```php
use HenryHt\BitwisePermission\Helpers\BitwiseHelper;

// Solo en runtime — NO en el archivo config
BitwiseHelper::combine(['view', 'create', 'update']); // → 13
BitwiseHelper::decode(13);           // → ['view', 'create', 'update']
BitwiseHelper::has(13, 'create');    // → true
BitwiseHelper::has(13, 'delete');    // → false
BitwiseHelper::add(13, 'delete');    // → 29
BitwiseHelper::remove(13, 'create'); // → 9
BitwiseHelper::total();              // → 1023
BitwiseHelper::all();                // → ['view' => 1, 'view_any' => 2, ...]
```

---

## Extender los bits

Los bits del 1 al 512 están reservados. **Empieza desde 1024**.

```php
$bits = [
    // Base (no modificar)
    'no_access' => 0,
    'view'      => 1,
    // ...
    'support'   => 512,

    // Extensiones
    'export'  => 1024,
    'import'  => 2048,
    'approve' => 4096,
    'publish' => 8192,
];
```

```php
auth()->user()->canCustom('export', 'reports.*');
```

```blade
@if(auth()->user()->canCustom('export'))
    <button>Exportar CSV</button>
@endif
```

---

## Comandos Artisan

```bash
# Instalador interactivo
php artisan bwp:install
php artisan bwp:install --migrate --seed --force

# Sincronizar rutas del proyecto
php artisan bwp:sync-routes --dry-run
php artisan bwp:sync-routes
php artisan bwp:sync-routes --type=api
```

---

## Tablas del paquete

| Tabla | Descripción |
|-------|-------------|
| `bwp_roles` | Roles base y de usuario |
| `bwp_permissions` | Combinaciones bitwise definidas |
| `bwp_app_routes` | Rutas en forma wildcard |
| `bwp_accesses` | Qué permiso tiene cada rol sobre cada ruta |
| `bwp_menus` | Árbol de navegación |
| `bwp_menu_role` | Visibilidad de menú por rol |

```php
'table_prefix' => 'myapp_', // cambiar antes de migrar
```

---

## UI Livewire

Accede en: `/bwp/roles`, `/bwp/permissions`, `/bwp/routes`, `/bwp/accesses`, `/bwp/menus`, `/bwp/menus/roles`

```php
'ui' => [
    'enabled'      => true,
    'route_prefix' => 'bwp',
    'middleware'   => ['web', 'auth'],
],
```

```bash
php artisan vendor:publish --tag=bwp-views  # personalizar vistas
```

---

## Estructura del paquete

```
laravel-bitwise-permission/
├── composer.json
├── README.md
├── LICENSE
├── config/
│   └── bitwise-permission.php
├── database/
│   ├── migrations/
│   │   └── ..._create_bitwise_permission_tables.php
│   └── seeders/
│       └── BitwisePermissionSeeder.php
├── resources/
│   ├── css/bwp.css
│   └── views/
│       ├── layout.blade.php
│       ├── livewire/
│       │   ├── roles/
│       │   ├── permissions/
│       │   ├── routes/
│       │   ├── accesses/
│       │   └── menus/
│       └── pages/
└── src/
    ├── BitwisePermissionServiceProvider.php
    ├── BitwisePermissionRoutes.php
    ├── Models/
    │   ├── Role.php
    │   ├── Permission.php
    │   ├── AppRoute.php
    │   ├── Access.php
    │   └── Menu.php
    ├── Traits/
    │   └── HasPermissionsTrait.php
    ├── Helpers/
    │   └── BitwiseHelper.php
    ├── Services/
    │   └── RoleCloneService.php
    ├── Middleware/
    │   ├── CheckPermissionMiddleware.php
    │   └── CheckBwpUiGateMiddleware.php
    ├── Observers/
    │   ├── RoleObserver.php
    │   ├── AppRouteObserver.php
    │   └── MenuObserver.php
    ├── Http/Livewire/
    │   ├── Roles/
    │   ├── Permissions/
    │   ├── Routes/
    │   ├── Accesses/
    │   └── Menus/
    └── Console/Commands/
        ├── InstallCommand.php
        └── SyncRoutesCommand.php
```

---

## Flujo completo de implementación

```
1.  composer require henry-ht/laravel-bitwise-permission
2.  php artisan bwp:install
3.  Agregar role_id a la tabla users
4.  Incluir HasPermissionsTrait en el modelo User
5.  Definir $bits, base_permissions y super_admin_role en config
6.  Definir role_permissions en config (por nombre de permiso)
7.  Definir gate en config (quién accede a la UI)
8.  php artisan bwp:sync-routes        ← detecta rutas del proyecto
9.  php artisan db:seed                ← siembra permisos, roles, menús
10. Ir a /bwp/accesses                 ← verificar accesos por rol
11. Ir a /bwp/menus/roles              ← configurar visibilidad de menú
12. Al crear usuarios → RoleCloneService::cloneForUser()
13. Personalizar por usuario → setPermission() / setMenuAccess()
```

---

## Licencia

TC Henry Commercial License v1.0

Copyright (c) 2026 TC Henry. Todos los derechos reservados.

Este software es propietario y confidencial. Al adquirir una licencia, se otorga el derecho no exclusivo e intransferible de usar el software en proyectos propios.

**Permitido:** instalar, usar y modificar para uso interno propio.

**Prohibido:** redistribuir, revender, sublicenciar, compartir con terceros o publicar el código fuente.

Para licencias: [contact@tchenry.com](mailto:contact@tchenry.com)

> Ver el archivo [LICENSE](LICENSE) para los términos completos.