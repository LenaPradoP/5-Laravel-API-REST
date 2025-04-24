<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SpreadCollection extends ResourceCollection
{

    protected $deckId;

    public function __construct($resource, $deckId)
    {
        parent::__construct($resource);
        $this->deckId = $deckId;
    }

    public function toArray(Request $request): array
    {
        return [
            'deck_id' => $this->deckId,
            'spreads' => $this->collection
        ];
    }
}