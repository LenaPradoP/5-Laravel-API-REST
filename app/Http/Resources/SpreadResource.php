<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpreadResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'deck_id' => $this->deck_id,
            'spread_type' => $this->spread_type,
            'creation_date' => $this->creation_date->format('Y-m-d H:i:s'),
            'card_count' => $this->whenLoaded('spreadCards', 
                function() {
                    return $this->spreadCards->count();
                },
                $this->spreadCards()->count()
            )
        ];
    }
}