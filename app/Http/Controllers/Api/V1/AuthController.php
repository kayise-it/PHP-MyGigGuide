<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiUserRegistrationService;
use App\Services\UserFirebaseLinkService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserFirebaseLinkService $firebaseLink,
        private readonly ApiUserRegistrationService $registration,
    ) {}
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = \App\Models\User::whereRaw('LOWER(username) = ?', [strtolower(trim($validated['username']))])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid username or password.',
            ], 422);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'This account is inactive.',
            ], 403);
        }

        $this->registration->ensureMemberCanCreateEvents($user);

        $token = $user->createToken($validated['device_name'] ?? 'mobile-app')->plainTextToken;

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    /**
     * Sign in with a Firebase ID token (mobile app account linked to Laravel users.id).
     */
    public function firebaseLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $user = $this->firebaseLink->resolveUserForFirebaseLogin($validated['id_token']);
        } catch (ModelNotFoundException) {
            return response()->json([
                'message' => 'No website account is linked to this Firebase sign-in. Log in with your username and password, then link Firebase in Settings.',
            ], 404);
        } catch (InvalidArgumentException|ValidationException $e) {
            $message = $e instanceof ValidationException
                ? collect($e->errors())->flatten()->first()
                : $e->getMessage();

            return response()->json(['message' => $message], 422);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'This account is inactive.',
            ], 403);
        }

        $token = $user->createToken($validated['device_name'] ?? 'mobile-app')->plainTextToken;

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    /**
     * Mobile-first signup — creates a Laravel user (role `user`) and returns a Sanctum token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'username' => ['nullable', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $result = $this->registration->register(
                name: $validated['name'],
                email: $validated['email'],
                password: $validated['password'],
                username: $validated['username'] ?? null,
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->flatten()->first() ?? 'Registration failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = $result['user'];
        $token = $user->createToken($validated['device_name'] ?? 'mobile-app')->plainTextToken;

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->userPayload($user),
            'message' => 'Account created. Your username is '.$result['username'].'.',
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles()->pluck('name')->values(),
            'firebase_linked' => $user->firebase_uid !== null,
        ];
    }
}
