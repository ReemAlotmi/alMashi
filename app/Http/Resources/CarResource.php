<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\CarClassification;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $carClassification= CarClassification::where('id',$this->classification_id)->first();

        $response = [
            'plate' => $this->plate,   
            'classification' => $carClassification->classification,
            'capacity' => $this->capacity,   
            'type' => $this->type,   
            'color' => $this->color,
        ];
        
        return $response;
    }
}
