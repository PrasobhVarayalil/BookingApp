<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        ['email' => $email, 'password' => $password] = $request->credentials();

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return $this->error('The provided credentials are incorrect.', 401);
        }

        return response()->json([
            'token' => $user->createToken('api')->plainTextToken,
            'user' => $user->only('id', 'name', 'email'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->noContent();
    }
}
