<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deck extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'last_used',
    ];

    protected $casts = [
        'last_used' => 'datetime',
    ];

    public function updateLastUsed(): void
    {
        $this->update(['last_used' => now()]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(DeckCard::class);
    }

    public function spreads(): HasMany
    {
        return $this->hasMany(Spread::class);
    }   
}