<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    // Listar todas as localidades
    public function index()
    {
        $query = Location::query();
        if (request()->has('active')) {
            $query->where('is_active', (bool)request()->query('active'));
        }
        return response()->json($query->get());
    }

    // Visualizar uma localidade
    public function show($id)
    {
        $location = Location::find($id);
        if (!$location) {
            return response()->json(['error' => 'Localidade não encontrada'], 404);
        }
        return response()->json($location);
    }

    // Cadastrar localidade
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'description' => 'nullable|string',
            'ip_equipamento' => 'nullable|string',
            'porta_equipamento' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);
        $location = Location::create($data);
        return response()->json($location, 201);
    }

    // Atualizar localidade
    public function update(Request $request, $id)
    {
        $location = Location::find($id);
        if (!$location) {
            return response()->json(['error' => 'Localidade não encontrada'], 404);
        }
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'latitude' => 'sometimes|required|numeric',
            'longitude' => 'sometimes|required|numeric',
            'description' => 'nullable|string',
            'ip_equipamento' => 'nullable|string',
            'porta_equipamento' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);
        $location->update($data);
        return response()->json($location);
    }

    /**
     * @todo validar se a localidade está relacionada a alguma locacao antes de excluir.
     */
    public function destroy($id)
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json(['error' => 'Localidade não encontrada'], 404);
        }

        $location->delete();
        return response()->json(['message' => 'Localidade excluída com sucesso']);
    }
}