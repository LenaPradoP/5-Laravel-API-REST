<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'number',
        'name',
        'suit',
        'element',
        'meaning'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'number' => 'integer',
    ];

    /**
     * Get the deck cards that contain this card.
     */
    public function deckCards(): HasMany
    {
        return $this->hasMany(DeckCard::class);
    }
}