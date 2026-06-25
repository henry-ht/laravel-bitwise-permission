# laravel-bitwise-permission

Sistema de permisos **bitwise** para Laravel. Roles, permisos, rutas y menús con UI Livewire opcional e instalación en minutos.

[![Laravel](https://img.shields.io/badge/Laravel-11%2B-red)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-blue)](https://livewire.laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-purple)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

---

## ¿Qué es un permiso bitwise?

En lugar de guardar una fila en base de datos por cada acción que un usuario puede hacer, un permiso bitwise es **un solo número entero** que representa múltiples acciones combinadas.

Cada acción es una potencia de 2 (un bit):

| Nombre | Valor | Descripción |
|--------|-------|-------------|
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

Con el número `13` sabes exactamente qué puede hacer ese rol en esa ruta.

> **Regla base absoluta:** sin el bit `view (1)` activo, ningún otro bit tiene efecto.
> El usuario no puede entrar a la vista aunque tenga otros bits activos.

---

## Instalación

```bash
composer require henry-ht/laravel-bitwise-permission
```

Luego ejecuta el instalador interactivo:

```bash
php artisan bwp:install
```

El comando te guía paso a paso:
- Publica la configuración
- Publica las migraciones
- Publica los assets CSS
- Pregunta si ejecutar `migrate`
- Pregunta si sembrar los datos base (permisos, roles, rutas)

### Instalación manual (paso a paso)

Si prefieres hacerlo tú mismo:

```bash
# 1. Publicar configuración
php artisan vendor:publish --tag=bwp-config

# 2. Publicar migraciones
php artisan vendor:publish --tag=bwp-migrations

# 3. Publicar assets CSS (para la UI)
php artisan vendor:publish --tag=bwp-assets

# 4. Ejecutar migraciones
php artisan migrate

# 5. Sembrar datos base
php artisan db:seed --class="HenryHt\BitwisePermission\Database\Seeders\BitwisePermissionSeeder"
```

### Publicar todo de una vez

```bash
php artisan vendor:publish --tag=bwp-all
```

---

## Configuración en tu modelo User

### 1. Agrega `role_id` a la tabla users

Crea una migración o agrégalo manualmente:

```bash
php artisan make:migration add_role_id_to_users_table --table=users
```

```php
Schema::table('users', function (Blueprint $table) {
    $table->foreignId('role_id')
          ->nullable()
          ->constrained('bwp_roles')
          ->nullOnDelete();
});
```

### 2. Incluye el trait en el modelo User

```php
<?php

namespace App\Models;

use HenryHt\BitwisePermission\Traits\HasPermissionsTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasPermissionsTrait;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];
}
```

### 3. El middleware se registra automáticamente

El paquete registra el alias `bwp.permission` de forma automática
al cargar el `ServiceProvider`. **No necesitas tocar `bootstrap/app.php`
ni el Kernel.**

Solo úsalo directamente en tus rutas:

```php
Route::middleware('bwp.permission')->group(function () {
    // rutas protegidas
});
```

Si quieres cambiar el alias, modifica `config/bitwise-permission.php`:

```php
'middleware' => [
    'alias' => 'bwp.permission', // cambia el nombre aquí
],
```

---

## Uso

### En rutas

El middleware verifica automáticamente que el usuario tenga el bit requerido
para la ruta actual. Si no tiene el bit `view (1)`, retorna 403.

```php
// Requiere solo view (bit 1) — mínimo para entrar a cualquier ruta
Route::middleware('bwp.permission')->group(function () {
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
});

// Requiere bit específico además de view
Route::middleware('bwp.permission:create')->group(function () {
    Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
});

Route::middleware('bwp.permission:update')->group(function () {
    Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
});

Route::middleware('bwp.permission:delete')->group(function () {
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
});
```

El middleware convierte automáticamente `leads.index` → `leads.*` para buscar
el permiso en la base de datos.

---

### En controladores

```php
<?php

namespace App\Http\Controllers;

use App\Models\Lead;

class LeadController extends Controller
{
    public function index()
    {
        // El middleware ya verificó view, pero puedes verificar viewAny
        if (! auth()->user()->canViewAny()) {
            // Solo puede ver sus propios leads
            $leads = Lead::where('assigned_to', auth()->id())->paginate(15);
        } else {
            // Puede ver todos
            $leads = Lead::paginate(15);
        }

        return view('leads.index', compact('leads'));
    }

    public function store(Request $request)
    {
        // Doble verificación (además del middleware)
        if (! auth()->user()->canCreate()) {
            abort(403);
        }

        Lead::create($request->validated());

        return redirect()->route('leads.index');
    }

    public function destroy(Lead $lead)
    {
        if (! auth()->user()->canDelete()) {
            abort(403);
        }

        $lead->delete();

        return redirect()->route('leads.index');
    }
}
```

---

### Verificar permiso para una ruta específica

Todos los métodos aceptan un nombre de ruta opcional.
Si no se pasa, usa el acceso activo del request actual (seteado por el middleware).

```php
$user = auth()->user();

// Verificar acceso activo del request actual
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

// Verificar acceso para una ruta específica (sin importar la ruta actual)
$user->canCreate('deals.*');   // ¿puede crear deals?
$user->canDelete('contacts.*'); // ¿puede eliminar contactos?
$user->canViewAny('leads.*');   // ¿puede ver el listado de leads?
```

---

### En vistas Blade

```blade
{{-- Botón de crear solo si tiene permiso --}}
@if(auth()->user()->canCreate())
    <a href="{{ route('leads.create') }}" class="btn btn-primary">
        Nuevo lead
    </a>
@endif

{{-- Acciones en tabla --}}
@foreach($leads as $lead)
    <tr>
        <td>{{ $lead->name }}</td>
        <td>
            @if(auth()->user()->canUpdate())
                <a href="{{ route('leads.edit', $lead) }}">Editar</a>
            @endif

            @if(auth()->user()->canDelete())
                <form action="{{ route('leads.destroy', $lead) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit">Eliminar</button>
                </form>
            @endif

            @if(auth()->user()->canChangeStatus())
                <button wire:click="changeStatus({{ $lead->id }})">Cambiar estado</button>
            @endif

            @if(auth()->user()->canAssign())
                <button wire:click="assign({{ $lead->id }})">Asignar</button>
            @endif
        </td>
    </tr>
@endforeach

{{-- Verificar permiso en otra ruta --}}
@if(auth()->user()->canCreate('deals.*'))
    {{-- El usuario puede crear deals, aunque estemos en la vista de leads --}}
@endif

{{-- Super admin --}}
@if(auth()->user()->hasTotalAccess())
    <span class="badge">Super Admin</span>
@endif
```

---

### En componentes Livewire

```php
<?php

namespace App\Http\Livewire\Leads;

use Livewire\Component;
use App\Models\Lead;

class LeadTableComponent extends Component
{
    public function delete(int $id): void
    {
        // Verificar permiso antes de actuar
        if (! auth()->user()->canDelete()) {
            $this->dispatch('notify', type: 'error', message: 'Sin permiso para eliminar.');
            return;
        }

        Lead::findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Lead eliminado.');
    }

    public function render()
    {
        return view('livewire.leads.table', [
            'leads'      => Lead::paginate(15),
            'canCreate'  => auth()->user()->canCreate(),
            'canUpdate'  => auth()->user()->canUpdate(),
            'canDelete'  => auth()->user()->canDelete(),
        ]);
    }
}
```

---

## BitwiseHelper

Utilidades para trabajar con valores bitwise directamente.

```php
use HenryHt\BitwisePermission\Helpers\BitwiseHelper;

// Combinar bits por nombre → valor entero
BitwiseHelper::combine(['view', 'create', 'update']);
// → 13  (1 + 4 + 8)

// Decodificar un valor → nombres de bits activos
BitwiseHelper::decode(13);
// → ['view', 'create', 'update']

BitwiseHelper::decode(0);
// → []

// Verificar si un valor tiene un bit activo
BitwiseHelper::has(13, 'create');  // → true
BitwiseHelper::has(13, 'delete');  // → false

// Agregar un bit a un valor existente
BitwiseHelper::add(13, 'delete');
// → 29  (13 + 16)

// Quitar un bit de un valor existente
BitwiseHelper::remove(13, 'create');
// → 9  (13 - 4)

// Valor total (todos los bits activos)
BitwiseHelper::total();
// → 1023  (1+2+4+8+16+32+64+128+256+512)

// Todos los bits disponibles con su valor
BitwiseHelper::all();
// → ['view' => 1, 'view_any' => 2, 'create' => 4, ...]
```

---

## Extender los bits

Puedes agregar tus propios bits en `config/bitwise-permission.php`.
Los bits del paquete van del 1 al 512. **Empieza desde 1024** para no colisionar.

```php
'bits' => [
    // Bits base (no modificar)
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

    // Tus bits personalizados
    'export'        => 1024,
    'import'        => 2048,
    'approve'       => 4096,
    'publish'       => 8192,
],
```

Luego úsalos con `canCustom()`:

```php
// En controlador o vista
auth()->user()->canCustom('export', 'reports.*');  // → true/false
auth()->user()->canCustom('approve', 'deals.*');   // → true/false
```

```blade
@if(auth()->user()->canCustom('export'))
    <button>Exportar CSV</button>
@endif
```

---

## Configurar permisos por rol

En `config/bitwise-permission.php` puedes definir los accesos de cada rol
sobre cada ruta. Esto se aplica al correr el seeder.

```php
'role_permissions' => [

    'super_admin' => [
        // Wildcard '*' = acceso total a todo (no se guarda en BD,
        // se resuelve en el trait directamente)
        '*' => 1 | 2 | 4 | 8 | 16 | 32 | 64 | 128 | 256 | 512, // 1023
    ],

    'admin' => [
        'leads.*'    => 1 | 2 | 4 | 8 | 16,        // view + viewAny + create + update + delete = 31
        'deals.*'    => 1 | 2 | 4 | 8 | 16,        // 31
        'contacts.*' => 1 | 2 | 4 | 8,             // view + viewAny + create + update = 15
        'profile.*'  => 1 | 8,                      // view + update = 9
        'password.*' => 1 | 8,                      // 9
    ],

    'agent' => [
        'leads.*'    => 1 | 2 | 4 | 8,             // sin delete = 15
        'deals.*'    => 1 | 2,                      // solo lectura = 3
        'contacts.*' => 1 | 2,                      // solo lectura = 3
        'profile.*'  => 1 | 8,                      // 9
        'password.*' => 1 | 8,                      // 9
    ],

    'viewer' => [
        'leads.*'    => 1 | 2,                      // solo ver = 3
        'deals.*'    => 1 | 2,                      // 3
        'contacts.*' => 1 | 2,                      // 3
        'profile.*'  => 1,                          // solo ver = 1
    ],

],
```

También puedes usar `BitwiseHelper::combine()` para mayor legibilidad:

```php
use HenryHt\BitwisePermission\Helpers\BitwiseHelper;

'role_permissions' => [
    'admin' => [
        'leads.*' => BitwiseHelper::combine(['view', 'view_any', 'create', 'update', 'delete']),
    ],
],
```

---

## Comandos Artisan

### `bwp:install`

Instalador interactivo completo.

```bash
php artisan bwp:install

# Opciones
php artisan bwp:install --migrate   # Ejecuta migrate sin preguntar
php artisan bwp:install --seed      # Ejecuta el seeder sin preguntar
php artisan bwp:install --force     # Sobreescribe archivos existentes
php artisan bwp:install --migrate --seed --force  # Todo automático
```

---

### `bwp:sync-routes`

Escanea todas las rutas nombradas del proyecto y las registra
automáticamente en `bwp_app_routes` en forma wildcard.

```bash
# Ver qué rutas detecta sin guardar nada
php artisan bwp:sync-routes --dry-run

# Registrar todas las rutas web
php artisan bwp:sync-routes

# Registrar rutas de API
php artisan bwp:sync-routes --type=api
```

Ejemplo: si tienes `leads.index`, `leads.show`, `leads.store`, `leads.update`, `leads.destroy`,
el comando registra una sola entrada: `leads.*`.

---

## Tablas del paquete

Con el prefijo por defecto `bwp_`:

| Tabla | Descripción |
|-------|-------------|
| `bwp_roles` | Roles del sistema |
| `bwp_permissions` | Combinaciones bitwise posibles |
| `bwp_app_routes` | Rutas registradas en forma wildcard |
| `bwp_accesses` | Qué permiso tiene cada rol sobre cada ruta |
| `bwp_menus` | Árbol de navegación (sidebar) |
| `bwp_menu_role` | Qué ítems de menú ve cada rol |

Puedes cambiar el prefijo en `config/bitwise-permission.php`:

```php
'table_prefix' => 'myapp_',
```

> Cambia el prefijo **antes** de ejecutar las migraciones.

---

## UI Livewire

El paquete incluye una interfaz para gestionar roles, permisos, rutas, accesos y menús.

Accede en: `/bwp/roles`, `/bwp/permissions`, `/bwp/routes`, `/bwp/accesses`, `/bwp/menus`

La UI requiere autenticación por defecto. Configurable en:

```php
'ui' => [
    'enabled'      => true,
    'route_prefix' => 'bwp',              // → /bwp/roles
    'middleware'   => ['web', 'auth'],    // protección de las rutas UI
],
```

### Personalizar las vistas

```bash
php artisan vendor:publish --tag=bwp-views
```

Las vistas quedan en `resources/views/vendor/bitwise-permission/`
y puedes modificarlas libremente sin afectar el paquete.

### Deshabilitar la UI

```php
'ui' => [
    'enabled' => false,
],
```

---

## Menú dinámico

El trait incluye `getMenu()` para cargar los ítems de menú visibles
para el rol del usuario autenticado.

```php
// En un componente Livewire del sidebar
public function render()
{
    return view('layouts.sidebar', [
        'menuItems' => auth()->user()->getMenu(),
    ]);
}
```

```blade
{{-- sidebar.blade.php --}}
@foreach($menuItems as $item)
    <a href="{{ route($item->patch) }}" class="nav-item">
        <i class="{{ $item->icon }}"></i>
        {{ $item->public_name }}
    </a>

    @foreach($item->childrenOrdered as $child)
        <a href="{{ route($child->patch) }}" class="nav-item nav-item--child">
            {{ $child->public_name }}
        </a>
    @endforeach
@endforeach
```

---

## Estructura del paquete

```
laravel-bitwise-permission/
├── LICENSE
├── composer.json
├── README.md
├── config/
│   └── bitwise-permission.php
├── database/
│   ├── migrations/
│   │   └── ..._create_bitwise_permission_tables.php
│   └── seeders/
│       └── BitwisePermissionSeeder.php
├── resources/
│   ├── css/
│   │   └── bwp.css
│   └── views/
│       ├── layout.blade.php
│       ├── livewire/              ← componentes Livewire
│       │   ├── roles/
│       │   ├── permissions/
│       │   ├── routes/
│       │   ├── accesses/
│       │   └── menus/
│       └── pages/                 ← páginas que montan los componentes
└── src/
    ├── Routes/
    │   ├── BitwisePermissionRoutes.php
    ├── Providers/
    │   ├── BitwisePermissionServiceProvider.php
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
    ├── Middleware/
    │   └── CheckPermissionMiddleware.php
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
1. composer require henry-ht/laravel-bitwise-permission
2. php artisan bwp:install
3. Agregar role_id a users
4. Agregar HasPermissionsTrait al modelo User
5. Registrar middleware bwp.permission en bootstrap/app.php
6. php artisan bwp:sync-routes  ← detecta tus rutas automáticamente
7. Ir a /bwp/roles → crear roles
8. Ir a /bwp/accesses → asignar permisos por rol y ruta
9. Ir a /bwp/menus → configurar el sidebar
10. Asignar role_id a los usuarios
```

---

## Licencia

MIT © [henry-ht](https://github.com/henry-ht)