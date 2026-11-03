<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function store(LoginRequest $request): JsonResponse
    {
        $user = $request->validateCredentials();

        return response()->json([
            'token' => $user->createApiToken(),
            'token_type' => 'Bearer',
            'user' => $user->toAuthArray(),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => $user->toAuthArray(),
        ]);
    }

    public function updatePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check((string) $request->string('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'كلمة المرور الحالية غير صحيحة.',
            ]);
        }

        $user->update([
            'password' => (string) $request->string('password'),
        ]);

        $user->tokens()->delete();

        return response()->json([
            'message' => 'تم تغيير كلمة المرور.',
            'token' => $user->createApiToken(),
            'token_type' => 'Bearer',
            'user' => $user->toAuthArray(),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج.',
        ]);
    }
}
