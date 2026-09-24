<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\OwnerLoginRequest;
use App\Http\Requests\Owner\OwnerRegisterRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OwnerAuthController extends Controller
{
    use ApiResponse;

    /**
     * Register new owner account.
     * @tags Owner Authentication
     */
    public function register(OwnerRegisterRequest $request): JsonResponse
    {
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone_number' => $request->phone_number,
            'role' => 'owner',
            'status_verification' => 'unverified',
        ]);

        $token = $user->createToken('owner-token', ['role:owner'])->plainTextToken;

        return $this->successResponse('Registrasi owner berhasil.', [
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status_verification' => $user->status_verification,
            ],
        ], 201);
    }

    /**
     * Login owner account.
     * @tags Owner Authentication
     */
    public function login(OwnerLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return $this->errorResponse('Kredensial tidak valid.', 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role !== 'owner') {
            Auth::logout();

            return $this->errorResponse('Akses ditolak. Endpoint ini khusus Pemilik Kost.', 403);
        }

        $token = $user->createToken('owner-token', ['role:owner'])->plainTextToken;

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
     * Get current authenticated owner profile.
     * @tags Owner Authentication
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
            'bank_name' => $user->bank_name,
            'bank_account_number' => $user->bank_account_number,
            'bank_account_holder' => $user->bank_account_holder,
            'profile_picture' => $user->profile_picture,
            'created_at' => $user->created_at,
        ]);
    }

    /**
     * Logout owner and revoke token.
     * @tags Owner Authentication
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse('Logout berhasil.');
    }
}
