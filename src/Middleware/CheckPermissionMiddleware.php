<?php

namespace HenryHt\BitwisePermission\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
 
/**
 * CheckPermissionMiddleware
 *
 * Verifica que el usuario tenga el bit requerido para la ruta actual.
 * Si el usuario es super admin (según config super_admin_role),
 * pasa directamente sin consultar la BD.
 *
 * Uso:
 *   Route::middleware('bwp.permission')          // requiere view
 *   Route::middleware('bwp.permission:create')   // requiere view + create
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
 
        // Super admin — acceso total sin consultar BD
        if ($user->isSuperAdmin()) {
            $bits  = config('bitwise-permission.bits', []);
            $total = array_sum(array_filter($bits, fn($v) => $v > 0));
            $user->setAccess($total);
            return $next($request);
        }
 
        // Resolver acceso para la ruta actual
        $access = $user->resolveAccess($routeName);
 
        // Setear acceso activo para uso en vistas y controllers
        $user->setAccess($access);
 
        // Verificar bit view — prerequisito absoluto
        $viewBit = config('bitwise-permission.bits.view', 1);
 
        if (! (($access & $viewBit) === $viewBit)) {
            return $this->unauthorized($request);
        }
 
        // Verificar bit adicional si se especificó
        if ($requiredBit !== 'view') {
            $bit = config("bitwise-permission.bits.{$requiredBit}", 0);
 
            if (! $bit || ! (($access & $bit) === $bit)) {
                return $this->unauthorized($request);
            }
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
