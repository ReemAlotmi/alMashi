<?php

namespace App\Models;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PassengerRide extends Model
{
    use HasFactory;
    protected $table = 'passenger_rides';

    
    protected $fillable = [
        'user_id',
        'cost',
        'ride_id',
        'status',
        'departure',
        'destination',
    ];



    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class);
    }

    public function requestRide(): HasMany
    {
        return $this->HasMany(RequestRide::class);
    }
}
