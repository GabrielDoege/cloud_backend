<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // Registro de usuário
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (User::where('email', $request->email)->exists()) {
            return response()->json(['error' => 'Já existe um usuário cadastrado com este e-mail'], 409);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Usuário registrado com sucesso'], 201);
    }

    /**
     * Realiza o login do usuário e retorna um token JWT.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Credenciais inválidas'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Não foi possível criar o token'], 500);
        }

        return response()->json(['token' => $token]);
    }

    // Validação de token
    public function validateToken(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json(['valid' => true])->header('X-User-Id', $user->id);
        } catch (JWTException $e) {
            return response()->json(['valid' => false, 'error' => 'Token inválido ou expirado'], 401);
        }
    }

    // Retorna dados do usuário autenticado
    public function me(Request $request)
    {
        // Se o gateway já repassou o X-User-Id, use-o diretamente
        $userId = $request->header('X-User-Id');
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                return response()->json(['user' => $user]);
            }
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        // Caso contrário, tente autenticar pelo token JWT
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json(['user' => $user]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token inválido ou expirado'], 401);
        }
    }
}
