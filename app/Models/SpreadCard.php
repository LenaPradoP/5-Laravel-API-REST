<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpreadCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'spread_id',
        'card_id',
        'position'
    ];

    public function spread(): BelongsTo
    {
        return $this->belongsTo(Spread::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
    
    public function toArray()
    {
        return [
            'id' => $this->id,
            'position' => $this->position,
            'card' => $this->card->toArray()
        ];
    }
}