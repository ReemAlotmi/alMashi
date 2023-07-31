<?php

namespace App\Models;

use App\Models\User;
use App\Models\DriverRate;
use App\Models\PassengerRate;
use App\Models\PassengerRide;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ride extends Model
{
    use HasFactory;



    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function passengerRide(): HasOne
    {
        return $this->hasOne(PassengerRide::class);
    }

    public function passengerRates(): HasMany
    {
        return $this->hasMany(PassengerRate::class);
    }

    public function driverRates(): HasMany
    {
        return $this->hasMany(DriverRate::class);
    }
}
