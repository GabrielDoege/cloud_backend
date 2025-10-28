<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Location;
use App\Bo\RentalPriceBo;
use App\Enums\EnumRentalStatus;
use App\Services\EquipamentoApiClient;
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
        // Validar se já existe uma locação ativa para este local
        // $existingActive = Rental::where('location_id', $request->location_id)
        //     ->where('status', EnumRentalStatus::ACTIVE)
        //     ->first();
        // if ($existingActive) {
        //     return response()->json(['error' => 'Já existe uma locação ativa para este local'], 422);
        // }

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

        if ($this->acionarBombaEquipamento($location, $duration)) {
            return response()->json($rental, 201);
        } else {
            return response()->json(['error' => 'Falha ao acionar a bomba do equipamento.'], 500);
        }
    }
    
    /**
     * Aciona a bomba do equipamento via API
     */
    private function acionarBombaEquipamento(Location $location, int $tempo): bool
    {
        if (!$location->ip_equipamento || !$location->porta_equipamento) {
            return false;
        }

        return EquipamentoApiClient::acionarBomba($location->ip_equipamento, $location->porta_equipamento, $tempo)['success'];
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

        $query->orderBy('id', 'desc');

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
            'status' => 'nullable|integer',
            'parar_bomba' => 'nullable|boolean',
        ]);

        if (isset($data['status'])) {
            if (!in_array($data['status'], EnumRentalStatus::getAllConsts(), true)) {
                return response()->json(['error' => 'Status inválido'], 422);
            }
        }

        // Converter 'end' para timestamp (Carbon) se enviado
        if (isset($data['end'])) {
            $endCarbon = Carbon::createFromFormat('d/m/Y H:i:s', $data['end']);
            // Validar se end é posterior ao start
            if ($endCarbon->lessThanOrEqualTo($rental->start)) {
                return response()->json(['error' => 'A término deve ser posterior ao início'], 422);
            }
            // Recalcular duration em segundos com base no start existente
            if ($rental->start) {
                $data['duration'] = $rental->start->diffInSeconds($endCarbon);
                $data['price']    = RentalPriceBo::calculate($data['duration']);
            } else {
                // Caso start não esteja disponível, não permitir atualizar end
                return response()->json(['error' => 'Start da locação não encontrado para calcular a duração'], 422);
            }
            // Substituir valor de end pelo Carbon convertido para garantir persistência correta
            $data['end'] = $endCarbon;

            if (isset($data['parar_bomba']) && $data['parar_bomba']) {
                $location = Location::find($rental->location_id);
                if ($location && $location->ip_equipamento && $location->porta_equipamento) {
                    EquipamentoApiClient::pararBomba($location->ip_equipamento, $location->porta_equipamento);
                }                
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