<?php

namespace App\Http\Resources;

use App\Models\Car;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\CarClassification;
use App\Http\Resources\CarResource;
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
        $response = [
            'name' => $this->name, 
            'profile_img' => $this->profile_img,
            'rating' => Helper::getRating($this->id),  
            'mobile_no' => $this->mobile_no,   
            'is_driver' => $this->is_driver,  
            'color' => $this->car->color?? null,
            'plate' => $this->car->plate?? null,   
            'classification' => $this->is_driver ? CarClassification::where('id', $this->car->classification_id)->value('classification') : null,
            'capacity' => $this->car->capacity?? null,   
            'type' => $this->car->type?? null,   
        ];
        
        return $response;
    }
}
