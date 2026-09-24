<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Kost extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'type',
        'address',
        'city',
        'latitude',
        'longitude',
        'description',
        'public_facility',
        'regulation',
    ];

    protected function casts(): array
    {
        return [
            'public_facility' => 'array',
            'regulation' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Kost $kost) {
            if (empty($kost->slug)) {
                $kost->slug = static::generateUniqueSlug($kost->name);
            }
        });

        static::updating(function (Kost $kost) {
            if ($kost->isDirty('name') && !$kost->isDirty('slug')) {
                $kost->slug = static::generateUniqueSlug($kost->name, $kost->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?string $exceptId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        do {
            $query = static::where('slug', $slug);
            if ($exceptId) {
                $query->where('id', '!=', $exceptId);
            }
            if ($query->exists()) {
                $slug = $original . '-' . $i++;
            } else {
                break;
            }
        } while (true);

        return $slug;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'kost_id', 'user_id')->withTimestamps();
    }
}
