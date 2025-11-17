<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissions
{
    /**
     * Maneja la petición entrante.
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'No autenticado.',
            ], 401);
        }

        // Convertimos "permission1|permission2" o "permission1,permission2"
        $permissions = collect($permissions)
            ->flatMap(fn ($p) => preg_split('/[|,]/', $p))
            ->map(fn ($p) => trim($p))
            ->filter()
            ->unique()
            ->toArray();

        // Si el usuario no tiene TODOS los permisos solicitados
        if (!$user->hasAllPermissions($permissions)) {
            return response()->json([
                'message' => 'No tienes permiso para realizar esta acción.',
                'required_permissions' => $permissions,
                'user_permissions' => $user->getAllPermissions()->pluck('name'),
            ], 403);
        }

        return $next($request);
    }
}
