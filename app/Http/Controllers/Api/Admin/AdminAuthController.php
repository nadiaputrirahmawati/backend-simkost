<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    use ApiResponse;

    /**
     * Login admin.
     * @tags Authentication
     */
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return $this->errorResponse('Kredensial tidak valid.', 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== 'admin') {
            Auth::logout();

            return $this->errorResponse('Akses ditolak. Endpoint ini khusus Super Admin.', 403);
        }

        $token = $user->createToken('admin-token', ['role:admin'])->plainTextToken;

        return $this->successResponse('Login berhasil.', [
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status_verification' => $user->status_verification,
            ],
        ]);
    }

    /**
     * Get current authenticated admin profile.
     * @tags Authentication
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse('Data pengguna berhasil diambil.', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone_number' => $user->phone_number,
            'status_verification' => $user->status_verification,
            'profile_picture' => $user->profile_picture,
            'created_at' => $user->created_at,
        ]);
    }

    /**
     * Logout admin and revoke token.
     * @tags Authentication
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse('Logout berhasil.');
    }
}
