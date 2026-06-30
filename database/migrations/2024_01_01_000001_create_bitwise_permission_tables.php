<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * laravel-bitwise-permission
 *
 * Crea todas las tablas del sistema de permisos en una sola migración.
 * El prefijo de tablas se lee desde config('bitwise-permission.table_prefix').
 *
 * Tablas (con prefijo bwp_ por defecto):
 *  1. bwp_roles
 *  2. bwp_permissions
 *  3. bwp_app_routes
 *  4. bwp_accesses
 *  5. bwp_menus
 *  6. bwp_menu_role
 *
 * Orden de ejecución respeta dependencias FK.
 * El down() elimina en orden inverso.
 */
return new class extends Migration
{
    protected string $prefix;

    public function __construct()
    {
        $this->prefix = config('bitwise-permission.table_prefix', 'bwp_');
    }

    public function up(): void
    {
        // ────────────────────────────────────────────────────
        // 1. ROLES
        // Unidad de agrupación de permisos.
        // is_base_role = true → plantilla global del paquete.
        // ────────────────────────────────────────────────────
        Schema::create("{$this->prefix}roles", function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();             // slug: 'admin', 'user'
            $table->string('public_name');                // 'Administrador'
            $table->string('description')->nullable();
            $table->boolean('is_base_role')->default(false);
            $table->foreignId('base_role_id')
                  ->nullable()
                  ->constrained("{$this->prefix}roles")
                  ->nullOnDelete();
       
            $table->timestamps();
                  
            $table->index('base_role_id');
            $table->index('name');
        });

        // ────────────────────────────────────────────────────
        // 2. PERMISSIONS
        // Valores bitwise combinados.
        // Bits: 1=view, 2=viewAny, 4=create, 8=update,
        //       16=delete, 32=restore, 64=forceDelete,
        //       128=changeStatus, 256=assign, 512=support
        // ────────────────────────────────────────────────────
        Schema::create("{$this->prefix}permissions", function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // 'basic access', 'read only'
            $table->unsignedInteger('access');            // valor bitwise único
            $table->timestamps();

            $table->unique('access');                     // cada combinación es única
            $table->index('access');
        });

        // ────────────────────────────────────────────────────
        // 3. APP_ROUTES
        // Rutas en forma wildcard: 'leads.*', 'deals.*'
        // Registra qué rutas del proyecto pueden tener permisos.
        // ────────────────────────────────────────────────────
        Schema::create("{$this->prefix}app_routes", function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();             // wildcard: 'leads.*'
            $table->string('type')->default('web');       // 'web' | 'api'
            $table->string('patch')->nullable();          // '/leads'
            $table->string('base_url')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index('name');
        });

        // ────────────────────────────────────────────────────
        // 4. ACCESSES
        // Relaciona: rol + ruta + permiso.
        // Un rol solo puede tener UN permiso por ruta.
        // ────────────────────────────────────────────────────
        Schema::create("{$this->prefix}accesses", function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')
                  ->constrained("{$this->prefix}roles")
                  ->cascadeOnDelete();
            $table->foreignId('route_id')
                  ->constrained("{$this->prefix}app_routes")
                  ->cascadeOnDelete();
            $table->foreignId('permission_id')
                  ->constrained("{$this->prefix}permissions")
                  ->cascadeOnDelete();
            $table->timestamps();

            // Un rol solo puede tener un permiso por ruta
            $table->unique(['role_id', 'route_id'], 'bwp_accesses_unique');
            $table->index('role_id');
        });

        // ────────────────────────────────────────────────────
        // 5. MENUS
        // Árbol de navegación del sidebar.
        // father_id = null → ítem raíz. Self-referencial.
        // ────────────────────────────────────────────────────
        Schema::create("{$this->prefix}menus", function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // slug: 'leads'
            $table->string('public_name');                // 'Leads'
            $table->string('patch')->nullable();          // ruta: 'leads.index'
            $table->string('icon')->nullable();           // clase de icono
            $table->unsignedSmallInteger('order')->default(0);
            $table->foreignId('father_id')
                  ->nullable()
                  ->constrained("{$this->prefix}menus")
                  ->nullOnDelete();
            $table->timestamps();

            $table->index('father_id');
            $table->index('order');
        });

        // ────────────────────────────────────────────────────
        // 6. MENU_ROLE
        // Pivot: qué ítems de menú ve cada rol.
        // disabled = true → oculto aunque tenga permiso.
        // ────────────────────────────────────────────────────
        Schema::create("{$this->prefix}menu_role", function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')
                  ->constrained("{$this->prefix}menus")
                  ->cascadeOnDelete();
            $table->foreignId('role_id')
                  ->constrained("{$this->prefix}roles")
                  ->cascadeOnDelete();
            $table->boolean('disabled')->default(false);
            $table->timestamps();

            $table->unique(['menu_id', 'role_id']);
            $table->index('role_id');
        });
    }

    public function down(): void
    {
        // Eliminar en orden inverso respetando FK
        Schema::dropIfExists("{$this->prefix}menu_role");
        Schema::dropIfExists("{$this->prefix}menus");
        Schema::dropIfExists("{$this->prefix}accesses");
        Schema::dropIfExists("{$this->prefix}app_routes");
        Schema::dropIfExists("{$this->prefix}permissions");
        Schema::dropIfExists("{$this->prefix}roles");
    }
};
