<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeckCard extends Model
{
    use HasFactory; //????

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'deck_id',
        'card_id',
        'position',
    ];

    /**
     * Get the deck that owns the deck card.
     */
    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    /**
     * Get the card for this deck card.
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}