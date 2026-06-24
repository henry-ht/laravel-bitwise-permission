<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Permissions') — BWP</title>
    <link rel="stylesheet" href="{{ asset('vendor/bitwise-permission/bwp.css') }}">
    @livewireStyles
</head>
<body class="bwp-page">

    <nav class="bwp-nav">
        <a href="{{ route('bwp.roles.index') }}" class="bwp-nav__brand">BWP</a>

        <a href="{{ route('bwp.roles.index') }}"
           class="bwp-nav__link {{ request()->routeIs('bwp.roles.*') ? 'active' : '' }}">
            Roles
        </a>
        <a href="{{ route('bwp.permissions.index') }}"
           class="bwp-nav__link {{ request()->routeIs('bwp.permissions.*') ? 'active' : '' }}">
            Permisos
        </a>
        <a href="{{ route('bwp.routes.index') }}"
           class="bwp-nav__link {{ request()->routeIs('bwp.routes.*') ? 'active' : '' }}">
            Rutas
        </a>
        <a href="{{ route('bwp.accesses.index') }}"
           class="bwp-nav__link {{ request()->routeIs('bwp.accesses.*') ? 'active' : '' }}">
            Accesos
        </a>
        <a href="{{ route('bwp.menus.index') }}"
           class="bwp-nav__link {{ request()->routeIs('bwp.menus.*') && ! request()->routeIs('bwp.menus.roles') ? 'active' : '' }}">
            Menús
        </a>
        <a href="{{ route('bwp.menus.roles') }}"
           class="bwp-nav__link {{ request()->routeIs('bwp.menus.roles') ? 'active' : '' }}">
            Menú por rol
        </a>
    </nav>

    <main class="bwp-main">
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>
