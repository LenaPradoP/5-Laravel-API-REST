<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};

class Spread extends Model
{
    use HasFactory;

    public const TYPE_SINGLE = 'first';
    public const TYPE_THREE = 'second';

    public const TYPES = [
        self::TYPE_SINGLE,
        self::TYPE_THREE
    ];

    protected $fillable = [
        'deck_id',
        'spread_type',
        'creation_date'
    ];

    protected $casts = [
        'creation_date' => 'datetime',
    ];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function spreadCards(): HasMany
    {
        return $this->hasMany(SpreadCard::class);
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'deck_id' => $this->deck_id,
            'spread_type' => $this->spread_type,
            'creation_date' => $this->creation_date->format('Y-m-d H:i:s'),
            'card_count' => $this->spreadCards()->count()
        ];
    }
}