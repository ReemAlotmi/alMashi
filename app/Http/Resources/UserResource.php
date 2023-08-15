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
        $carClassification= CarClassification::where('id',$this->classification_id)->first();
        $response = [
            'name' => $this->name, 
            'profile_img' => $this->profile_img,
            'rating' => Helper::getRating($this->id),  
            'mobile_no' => $this->mobile_no,   
            'is_driver' => $this->is_driver,  
            'color' => $this->car->color?? null,
            'plate' => $this->car->plate?? null,   
            'classification' => $carClassification->classification,
            'capacity' => $this->capacity,   
            'type' => $this->type,   
        ];
        
        return $response;
    }
}
