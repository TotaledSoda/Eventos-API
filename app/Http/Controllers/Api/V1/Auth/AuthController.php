<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Registro de usuario.
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create($validated);

        // Asignar rol base
        $user->assignRole('user');

        // Crear token Sanctum
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado correctamente.',
            'data'    => [
                'user'  => $user,
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Login de usuario.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciales incorrectas.',
            ], 422);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Opcional: eliminar tokens previos
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso.',
            'data'    => [
                'user'  => $user->load('roles', 'permissions'),
                'token' => $token,
            ],
        ]);
    }

    /**
     * Información del usuario autenticado.
     */
    public function me(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return response()->json([
            'data' => $user->load('roles', 'permissions'),
        ]);
    }

    /**
     * Logout (revoca el token actual).
     */
    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }
}
