<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone_number',
        'no_ktp',
        'npwp',
        'gender',
        'work',
        'tgl_lahir',
        'address',
        'marital_status',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'balance',
        'profile_picture',
        'ktp_picture',
        'ktp_picture_person',
        'status_verification',
        'rejection_feedback',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'tgl_lahir' => 'date',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Owner relations
    public function kosts(): HasMany
    {
        return $this->hasMany(Kost::class, 'owner_id');
    }

    public function ownedContracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'owner_id');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class, 'owner_id');
    }

    // Tenant relations
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    // Convenience: rooms this user is currently renting (via active contracts)
    public function rentedRooms(): HasManyThrough
    {
        return $this->hasManyThrough(Room::class, Contract::class, 'user_id', 'id', 'id', 'room_id');
    }
}
