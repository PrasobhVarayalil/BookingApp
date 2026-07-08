<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private ActivityLogger $activity) {}

    public function login(LoginRequest $request): JsonResponse
    {
        ['email' => $email, 'password' => $password] = $request->credentials();

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return $this->error('The provided credentials are incorrect.', 401);
        }

        $token = $user->createToken('api')->plainTextToken;

        $this->activity->log('Logged in via API', causer: $user, properties: ['token_name' => 'api']);

        return response()->json([
            'token' => $token,
            'user' => $user->only('id', 'name', 'email'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user !== null) {
            $this->activity->log('Logged out via API', causer: $user);
            $user->currentAccessToken()?->delete();
        }

        return $this->noContent();
    }
}
