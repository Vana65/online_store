<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Controller\PersonalAccessToken;
use App\Http\Requests\User\ForgotPasswordRequest;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserAuthController extends Controller
{
    // Register//
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'message' => __('keywords.register_success'),
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // login//
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = $validated['email'];
        $column = 'email';
        $user = User::query()->where($column, $email)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => __('keywords.invalid_credentials'),
            ], 401);
        }

        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'message' => __('keywords.login_success'),
            'user' => $user,
            'token' => $token,
        ]);
    }

    // dashboard//
    public function me(): JsonResponse
    {
        return response()->json(auth()->guard('api')->user());
    }

    // forgot Password//
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink(
            $request->validated()
        );

        return response()->json([
            'message' => __('keywords.forgot_password_success'),
        ]);
    }

    // reset Password//
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->validated(),

            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return response()->json([
            'message' => __('keywords.reset_password_success'),
        ]);
    }

    // logout//
    public function logout(): JsonResponse
    {
        /** @var User|null $user */
        $user = auth()->guard('web')->user();
        /** @var PersonalAccessToken|null $token */
        $token = $user?->currentAccessToken();

        $token?->delete();

        return response()->json([
            'message' => __('keywords.logout_success'),
        ]);
    }
}
