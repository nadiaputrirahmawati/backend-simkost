<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kost_id',
        'room_number',
        'room_size',
        'price',
        'deposit_amount',
        'status',
        'room_facility',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'room_facility' => 'array',
        ];
    }

    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}
