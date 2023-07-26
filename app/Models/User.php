<?php

namespace App\Models;

use App\Models\Car;
use App\Models\Otp;
use App\Models\Ride;
use App\Models\DriverRate;
use App\Models\PassengerRate;
use App\Models\PassengerRide;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'id',	
        'mobile_no',	
        'name',	
        'rating',	
        'porfile_img',
    ];

    public function car(): HasOne
    {
        return $this->hasOne(Car::class);
    }

    public function otp(): HasOne
    {
        return $this->hasOne(Otp::class);
    }

    public function passengerRides(): HasMany
    {
        return $this->hasMany(PassengerRide::class);
    }

    public function Rides(): HasMany
    {
        return $this->hasMany(Ride::class);
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
