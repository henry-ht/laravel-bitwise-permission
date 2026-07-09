<p align="center">
  <img src="https://bitwise.tchenry.com/og-image.png" alt="Laravel Bitwise Permission" width="760">
</p>

<h1 align="center">Laravel Bitwise Permission</h1>

<p align="center">
  Un sistema de permisos rápido y ligero en base de datos para Laravel.<br>
  Roles, permisos, rutas y menús — resueltos desde un solo número entero, no desde una tabla de relaciones.
</p>

<p align="center">
  <a href="https://packagist.org/packages/henry-ht/laravel-bitwise-permission"><img src="https://img.shields.io/packagist/v/henry-ht/laravel-bitwise-permission.svg?style=flat-square" alt="Latest Version"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square" alt="License"></a>
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777bb4.svg?style=flat-square" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-11%20%7C%2012%20%7C%2013-ff2d20.svg?style=flat-square" alt="Laravel">
</p>

<p align="center">
  <b><a href="https://bitwise.tchenry.com">Documentación</a></b> ·
  <a href="https://bitwise.tchenry.com/docs/installation">Instalación</a> ·
  <a href="https://bitwise.tchenry.com/docs/examples">Ejemplos</a> ·
  <a href="README.md">English</a>
</p>

> Este README es un resumen en español. La documentación completa y actualizada vive en **[bitwise.tchenry.com](https://bitwise.tchenry.com)**.

---

## ¿Qué es un permiso bitwise?

En lugar de guardar una fila en base de datos por cada acción que un usuario puede hacer, un permiso bitwise es **un solo número entero** que representa múltiples acciones combinadas. Cada acción es una potencia de 2 (un bit):

```
view + create + update = 1 + 4 + 8 = 13
```

Verificar el acceso es una operación `AND` a nivel de CPU, no una consulta a base de datos:

```php
$access & $bit === $bit
```

> **Regla base absoluta:** sin el bit `view (1)` activo, ningún otro bit tiene efecto. El usuario no puede entrar a la vista aunque tenga otros bits activos.

## Características

- 🔢 Núcleo bitwise — combina y compara permisos con aritmética de enteros, sin joins en tiempo real.
- 🧩 Permisos por ruta — protege rutas nombradas con convención wildcard (`leads.*`, `deals.*`).
- 🧭 Menús con visibilidad por rol — árbol de navegación con propagación automática padre/hijo.
- 👑 Bypass de super admin — acceso total sin tocar la base de datos.
- 🧬 Roles por usuario — `RoleCloneService` clona un rol base por usuario.
- 🖥️ UI Livewire opcional — administra roles, permisos, rutas, accesos y menús desde `/bwp/*`.
- ⚙️ Comandos Artisan — `bwp:install` y `bwp:sync-routes` configuran el proyecto en minutos.
- 🧱 Todo por configuración — bits, permisos base, roles, rutas y menús en un solo archivo de config.

## Instalación

```bash
composer require henry-ht/laravel-bitwise-permission
php artisan bwp:install
```

```php
use HenryHt\BitwisePermission\Traits\HasPermissionsTrait;

class User extends Authenticatable
{
    use HasPermissionsTrait;
}
```

```php
Route::middleware('bwp.permission:create')->group(function () {
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
});
```

```blade
@if(auth()->user()->canCreate())
    <a href="{{ route('leads.create') }}">Nuevo lead</a>
@endif
```

Guía completa de instalación manual, configuración de `role_id`, bits, permisos base, super admin, menús y comandos: **[bitwise.tchenry.com/docs/installation](https://bitwise.tchenry.com/docs/installation)**.

## Documentación

| Guía | Descripción |
|---|---|
| [Instalación](https://bitwise.tchenry.com/docs/installation) | Requisitos, comando de instalación y setup manual |
| [Configuración](https://bitwise.tchenry.com/docs/configuration) | Bits, permisos base, super admin, prefijo de tablas |
| [Middleware](https://bitwise.tchenry.com/docs/middleware) | Protección de rutas y la regla del bit `view` |
| [Roles](https://bitwise.tchenry.com/docs/roles) | Roles base, roles por usuario, `RoleCloneService` |
| [Permisos](https://bitwise.tchenry.com/docs/permissions) | Verificar, asignar y combinar permisos en runtime |
| [Menús](https://bitwise.tchenry.com/docs/menus) | Árboles de navegación con visibilidad por rol |
| [Ejemplos](https://bitwise.tchenry.com/docs/examples) | Flujos completos: creación de usuarios, bits custom, Livewire |
| [Migración](https://bitwise.tchenry.com/docs/upgrading) | Notas de actualización entre versiones |

## Requisitos

- PHP 8.2+
- Laravel 11, 12 o 13
- Livewire 3 o 4 (solo si usas la UI de administración incluida)

## Changelog

Consulta [CHANGELOG.md](CHANGELOG.md) para el historial de versiones.

## Contribuir

Las contribuciones son bienvenidas. Lee [CONTRIBUTING.md](CONTRIBUTING.md) antes de abrir un pull request.

## Seguridad

Si encuentras una vulnerabilidad de seguridad, revisa [SECURITY.md](SECURITY.md) para el proceso de divulgación — no abras un issue público.

## Créditos

- [Henry HT](https://github.com/henry-ht)
- [Todos los contribuidores](https://github.com/henry-ht/laravel-bitwise-permission/contributors)

## Licencia

MIT License. Consulta [LICENSE](LICENSE) para más información.
