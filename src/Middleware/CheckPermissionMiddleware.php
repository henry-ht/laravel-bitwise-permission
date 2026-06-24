<?php

namespace HenryHt\BitwisePermission\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckPermissionMiddleware
 *
 * Verifica que el usuario autenticado tenga al menos el bit 'view'
 * para la ruta actual. Setea el acceso activo en el modelo User.
 *
 * Uso en rutas:
 *   Route::middleware('bwp.permission')->group(function () { ... });
 *
 * Uso con bit específico:
 *   Route::middleware('bwp.permission:create')->group(function () { ... });
 */
class CheckPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $requiredBit = 'view'): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user      = Auth::user();
        $routeName = $request->route()?->getName();

        if (! $routeName) {
            return $next($request);
        }

        // Resolver acceso para la ruta actual
        $access = $user->resolveAccess($routeName);

        // Setear acceso activo en el usuario para uso en vistas/controllers
        $user->setAccess($access);

        // Verificar bit requerido
        $bit = config("bitwise-permission.bits.{$requiredBit}", 1);

        // Sin view (1) no se puede entrar — prerequisito absoluto
        $viewBit = config('bitwise-permission.bits.view', 1);

        if (! (($access & $viewBit) === $viewBit)) {
            return $this->unauthorized($request);
        }

        // Verificar bit adicional si se especificó
        if ($requiredBit !== 'view' && ! (($access & $bit) === $bit)) {
            return $this->unauthorized($request);
        }

        return $next($request);
    }

    protected function unauthorized(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        abort(403);
    }
}
