<?php

namespace HenryHt\BitwisePermission\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckBwpUiGateMiddleware
 *
 * Protege todas las rutas de la UI del paquete.
 * Evalúa el gate 'bwp-ui' definido en BitwisePermissionRoutes.
 *
 * El callback lo define el usuario en config/bitwise-permission.php:
 *
 *   'gate' => function ($user) {
 *       return $user->hasRole('super_admin');
 *   },
 */
class CheckBwpUiGateMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (Gate::denies('bwp-ui')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}