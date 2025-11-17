<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Devuelve los roles y permisos del usuario autenticado.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Nombres de roles
        $roles = $user->roles->pluck('name')->toArray();

        // Nombres de permisos (incluye permisos por rol)
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        return response()->json([
            'data' => [
                'user'        => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ],
                'roles'       => $roles,
                'permissions' => $permissions,
            ],
        ]);
    }
}
