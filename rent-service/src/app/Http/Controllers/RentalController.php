<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Location;
use App\Bo\RentalPriceBo;
use App\Enums\EnumRentalStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RentalController extends Controller
{
    // Criar locação
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location_id' => 'required|exists:locations,id',
            'duration_seconds' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userId = $request->header('X-User-Id');
        if (!$userId) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }
        $location = Location::find($request->location_id);
        $duration = $request->duration_seconds;
        $start    = Carbon::now();
        $end      = (clone $start)->addSeconds($duration);
        $price    = RentalPriceBo::calculate($duration);

        $rental = Rental::create([
            'user_id'     => $userId,
            'location_id' => $location->id,
            'start'       => $start,
            'end'         => $end,
            'duration'    => $duration,
            'price'       => $price,
            'status'      => EnumRentalStatus::ACTIVE
        ]);

        return response()->json($rental, 201);
    }

    // Histórico de locações do usuário
    public function history(Request $request)
    {
        $userId = $request->header('X-User-Id');

        if (!$userId) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        $query = Rental::with('location')->where('user_id', $userId);

        if ($request->has('id')) {
            $query->where('id', $request->id);
        }

        $rentals = $query->get();
        return response()->json($rentals);
    }

    // Atualizar locação
    public function update(Request $request, $id)
    {
        $userId = $request->header('X-User-Id');

        if (!$userId) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        $rental = Rental::where('id', $id)->where('user_id', $userId)->first();
        
        if (!$rental) {
            return response()->json(['error' => 'Locação não encontrada ou não pertence ao usuário'], 404);
        }

        $data = $request->validate([
            'end' => ['nullable', 'date_format:"d/m/Y H:i:s"'],
            'duration' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        if (isset($data['status'])) {
            if (!in_array($data['status'], EnumRentalStatus::getAllConsts(), true)) {
                return response()->json(['error' => 'Status inválido'], 422);
            }
        }

        // Converter 'end' para timestamp (Carbon) se enviado
        if (isset($data['end'])) {
            $data['end'] = Carbon::createFromFormat('d/m/Y H:i:s', $data['end']);
            // Validar se end é posterior ao start
            if ($data['end']->lessThanOrEqualTo($rental->start)) {
                return response()->json(['error' => 'A término deve ser posterior ao início'], 422);
            }
        }

        $rental->update($data);
        return response()->json($rental);
    }

    // Calcular valor da locação sem criar registro
    public function calculatePrice(Request $request)
    {
        $request->validate([
            'duration_seconds' => 'required|integer|min:1',
        ]);
        $price = RentalPriceBo::calculate($request->duration_seconds);
        return response()->json(['price' => $price]);
    }
}
