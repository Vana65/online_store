<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AdminAuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = $validated['email'];
        $column = 'email';
        $admin = Admin::query()->where($column, $email)->first();

        if (! $admin || ! Hash::check($validated['password'], $admin->password)) {
            return response()->json([
                'message' => __('keywords.invalid_credentials'),
            ], 401);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => __('keywords.login_success'),
            'admin' => $admin,
            'token' => $token,
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json(auth()->guard('admin')->user());
    }

    public function logout(): JsonResponse
    {
        /** @var Admin|null $admin */
        $admin = auth()->guard('admin')->user();
        /** @var PersonalAccessToken|null $token */
        $token = $admin?->currentAccessToken();

        $token?->delete();

        return response()->json([
            'message' => __('keywords.logout_success'),
        ]);
    }
}
