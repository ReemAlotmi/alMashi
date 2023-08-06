<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestRide extends Model
{
    use HasFactory;
    protected $table = 'requests';

    protected $fillable = [
        'id',	
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
}
