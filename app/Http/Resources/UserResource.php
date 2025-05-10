<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'birthdate' => $this->birthdate->format('d/m/Y'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        
        // Include roles if the requesting user is an admin
        if ($request->user() && $request->user()->hasRole('admin')) {
            $data['roles'] = $this->getRoleNames()->toArray();
        }
        
        return $data;
    }
}