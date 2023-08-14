<?php

namespace App\Models;

use App\Models\Ride;
use App\Models\User;
use App\Models\PassengerRide;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'ride_id',
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

    public function passengerRide(): BelongsTo
    {
        return $this->belongsTo(PassengerRide::class);
    }
}
