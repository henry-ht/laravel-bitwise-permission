<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Permissions') — BWP</title>
    <link rel="stylesheet" href="{{ asset('vendor/bitwise-permission/bwp.css') }}">
    @livewireStyles
</head>
<body style="background:#0f1117;margin:0;padding:2rem;font-family:'Inter',system-ui,sans-serif;">

    <nav style="display:flex;align-items:center;gap:0.5rem;margin-bottom:2rem;flex-wrap:wrap;border-bottom:1px solid #2e3347;padding-bottom:1rem;">
        <span style="font-size:0.8rem;font-weight:800;color:#e2e8f0;margin-right:0.75rem;letter-spacing:-0.01em;">BWP</span>
        @foreach([
            ['bwp.roles.index',       'bwp.roles.*',       'Roles'],
            ['bwp.permissions.index', 'bwp.permissions.*', 'Permisos'],
            ['bwp.routes.index',      'bwp.routes.*',      'Rutas'],
            ['bwp.accesses.index',    'bwp.accesses.*',    'Accesos'],
            ['bwp.menus.index',       'bwp.menus.*',       'Menús'],
        ] as [$route, $pattern, $label])
            <a href="{{ route($route) }}"
               style="padding:0.35rem 0.75rem;border-radius:0.375rem;font-size:0.8rem;font-weight:500;text-decoration:none;
                      color:{{ request()->routeIs($pattern) ? '#3b82f6' : '#7c8bab' }};
                      background:{{ request()->routeIs($pattern) ? 'rgba(59,130,246,0.1)' : 'transparent' }};">
                {{ $label }}
            </a>
        @endforeach
    </nav>

    @yield('content')

    @livewireScripts
</body>
</html>
