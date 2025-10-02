<?php

namespace App\Http\Controllers;

use App\Enums\EnumRentalStatus;
use App\Models\Review;
use App\Models\Rental;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // Incluir avaliação
    public function store(Request $request)
    {
        $userId = $request->header('X-User-Id');
        $data = $request->validate([
            'rental_id' => 'required|exists:rentals,id',
            'rating' => 'required|integer|min:0|max:5',
            'comment' => 'nullable|string',
        ]);

        $rental = Rental::find($data['rental_id']);

        if (!$rental) {
            return response()->json(['error' => 'Locação não encontrada'], 403);
        }

        if ($rental->user_id != $userId) {
            return response()->json(['error' => 'Locação não pertence ao usuário'], 403);
        }

        if ($rental->status != EnumRentalStatus::FINISHED) {
            return response()->json(['error' => 'Só é possível avaliar locações finalizadas'], 422);
        }

        // Não permitir review duplicada para o mesmo rental_id
        if (Review::where('rental_id', $data['rental_id'])->exists()) {
            return response()->json(['error' => 'Já existe uma avaliação para esta locação'], 422);
        }

        $review = Review::create($data);

        return response()->json($review, 201);
    }

    // Listar avaliações por rental_id
    public function index(Request $request)
    {
        $userId = $request->header('X-User-Id');

        $request->validate([
            'rental_id' => 'required|exists:rentals,id',
        ]);
        
        $rental = Rental::where('id', $request->rental_id)->where('user_id', $userId)->first();

        if (!$rental) {
            return response()->json(['error' => 'Locação não encontrada ou não pertence ao usuário'], 404);
        }
        
        $reviews = Review::where('rental_id', $request->rental_id)->get();
        return response()->json($reviews);
    }
}
