<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\OrganizerStoreRequest;
use App\Http\Requests\Organizer\OrganizerUpdateRequest;
use App\Models\Organizer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OrganizerController extends Controller
{
    /**
     * Listado de organizadores (paginado).
     * Permiso: organizers.view
     */
    public function index(Request $request)
    {
        $query = Organizer::with('user')
            ->orderByDesc('created_at');

        // Filtros simples opcionales
        if ($request->filled('is_verified')) {
            $query->where('is_verified', filter_var($request->get('is_verified'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        $organizers = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $organizers,
        ]);
    }

    /**
     * Crear un organizador.
     * Permiso: organizers.edit (no tenemos organizers.create definido)
     */
    public function store(OrganizerStoreRequest $request)
    {
        $data = $request->validated();

        $organizer = Organizer::create($data);

        return response()->json([
            'message'   => 'Organizador creado correctamente.',
            'data'      => $organizer->load('user'),
        ], 201);
    }

    /**
     * Detalle de un organizador.
     * Permiso: organizers.view
     */
    public function show(Organizer $organizer)
    {
        return response()->json([
            'data' => $organizer->load('user'),
        ]);
    }

    /**
     * Actualizar datos del organizador.
     * Permiso: organizers.edit
     */
    public function update(OrganizerUpdateRequest $request, Organizer $organizer)
    {
        $data = $request->validated();

        $organizer->update($data);

        return response()->json([
            'message' => 'Organizador actualizado correctamente.',
            'data'    => $organizer->load('user'),
        ]);
    }

    /**
     * Verificar organizador.
     * Permiso: organizers.verify
     */
    public function verify(Organizer $organizer)
    {
        if ($organizer->is_verified) {
            return response()->json([
                'message' => 'El organizador ya está verificado.',
                'data'    => $organizer,
            ]);
        }

        $organizer->update([
            'is_verified' => true,
            'verified_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Organizador verificado correctamente.',
            'data'    => $organizer,
        ]);
    }

    /**
     * Quitar verificación (opcional).
     * Permiso: organizers.verify
     */
    public function unverify(Organizer $organizer)
    {
        if (!$organizer->is_verified) {
            return response()->json([
                'message' => 'El organizador no está verificado.',
                'data'    => $organizer,
            ]);
        }

        $organizer->update([
            'is_verified' => false,
            'verified_at' => null,
        ]);

        return response()->json([
            'message' => 'Verificación del organizador removida.',
            'data'    => $organizer,
        ]);
    }
}
