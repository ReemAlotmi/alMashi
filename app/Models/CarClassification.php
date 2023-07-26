<?php

namespace App\Models;

use App\Models\Car;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarClassification extends Model
{
    use HasFactory;


    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
