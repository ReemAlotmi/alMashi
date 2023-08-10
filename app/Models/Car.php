<?php

namespace App\Models;

use App\Models\User;
use App\Models\CarClassification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Car extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',	
        'type',	
        'classification_id',	
        'capacity',	
        'plate',
        'color',
    ];

    public function classification(): HasOne
    {
        return $this->hasOne(CarClassification::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
