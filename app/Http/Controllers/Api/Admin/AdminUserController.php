<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOwnerRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    use ApiResponse;

    /**
     * List all users with optional filters.
     * @tags User Management
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status_verification')) {
            $query->where('status_verification', $request->status_verification);
        }

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone_number', 'like', "%{$keyword}%");
            });
        }

        $users = $query->latest()->paginate($request->input('per_page', 15));

        return $this->paginateResponse(UserResource::collection($users), 'Daftar pengguna berhasil diambil.');
    }

    /**
     * Create a new owner (kost landlord) account.
     * @tags User Management
     */
    public function storeOwner(StoreOwnerRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone_number' => $data['phone_number'],
            'role' => 'owner',
            'status_verification' => $data['auto_verified'] ?? false ? 'verified' : 'unverified',
            'bank_name' => $data['bank_name'] ?? null,
            'bank_account_number' => $data['bank_account_number'] ?? null,
            'bank_account_holder' => $data['bank_account_holder'] ?? null,
        ]);

        return $this->successResponse('Akun owner berhasil dibuat.', new UserResource($user), JsonResponse::HTTP_CREATED);
    }

    /**
     * Get user detail by ID.
     * @tags User Management
     */
    public function show(string $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return $this->errorResponse('Pengguna tidak ditemukan.', JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->successResponse('Data pengguna berhasil diambil.', new UserResource($user));
    }

    /**
     * Update user data.
     * @tags User Management
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return $this->errorResponse('Pengguna tidak ditemukan.', JsonResponse::HTTP_NOT_FOUND);
        }

        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return $this->successResponse('Data pengguna berhasil diperbarui.', new UserResource($user->fresh()));
    }

    /**
     * Delete a user.
     * @tags User Management
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        if ($request->user()->id === $id) {
            return $this->errorResponse('Admin tidak dapat menghapus akun sendiri.', JsonResponse::HTTP_FORBIDDEN);
        }

        $user = User::find($id);

        if (! $user) {
            return $this->errorResponse('Pengguna tidak ditemukan.', JsonResponse::HTTP_NOT_FOUND);
        }

        $user->delete();

        return $this->successResponse('Pengguna berhasil dihapus.');
    }
}
