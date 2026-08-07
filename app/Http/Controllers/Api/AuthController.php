<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new student account.
     *
     * Creates a student account and returns a Sanctum token for immediately
     * authenticated API calls.
     *
     * @group Authentication
     *
     * @unauthenticated
     *
     * @bodyParam name string required The student's full name. Example: Sara Benali
     * @bodyParam email string required A valid, unique email address. Example: sara@example.com
     * @bodyParam password string required Password, minimum 8 characters. Example: secret-password
     *
     * @response status=201 scenario="success" {
     *  "message": "Registration successful.",
     *  "user": {"id": 1, "name": "Sara Benali", "email": "sara@example.com", "role": "student"},
     *  "token": "1|abcdef123456"
     * }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => UserRole::Student,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Log in and obtain a Sanctum token.
     *
     * @group Authentication
     *
     * @unauthenticated
     *
     * @bodyParam email string required The account email. Example: sara@example.com
     * @bodyParam password string required The account password. Example: secret-password
     *
     * @response status=200 {
     *  "message": "Login successful.",
     *  "user": {"id": 1, "name": "Sara Benali", "email": "sara@example.com", "role": "student"},
     *  "token": "eyJ..."
     * }
     * @response status=401 {
     *  "message": "Invalid credentials."
     * }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Log out the current session.
     *
     * Revokes the current Sanctum token.
     *
     * @group Authentication
     *
     * @response status=204
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }

    /**
     * Show the authenticated user's profile.
     *
     * @group Authentication
     *
     * @response {
     *  "id": 1,
     *  "name": "Sara Benali",
     *  "email": "sara@example.com",
     *  "role": "student"
     * }
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()));
    }
}
