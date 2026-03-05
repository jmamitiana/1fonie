<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'description',
        'specialty',
        'service_categories',
        'service_areas',
        'license_number',
        'license_expiry',
        'hourly_rate',
        'latitude',
        'longitude',
        'stripe_account_id',
        'is_verified',
        'is_available',
        'rating',
        'total_reviews',
    ];

    protected $casts = [
        'service_categories' => 'array',
        'service_areas' => 'array',
        'license_expiry' => 'date',
        'hourly_rate' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_verified' => 'boolean',
        'is_available' => 'boolean',
        'rating' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function missions()
    {
        return $this->hasMany(Mission::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
