<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'provider_id',
        'title',
        'description',
        'category',
        'location_city',
        'location_address',
        'location_country',
        'location_zipcode',
        'latitude',
        'longitude',
        'intervention_date',
        'intervention_time',
        'price',
        'platform_fee',
        'status',
        'attachments',
        'cancellation_reason',
        'assigned_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'intervention_date' => 'date',
        'intervention_time' => 'datetime:H:i',
        'price' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUSES = [
        'draft',
        'open',
        'in_review',
        'assigned',
        'in_progress',
        'completed',
        'cancelled',
        'disputed',
    ];

    const CATEGORIES = [
        'it_support',
        'plumbing',
        'electrical',
        'network_installation',
        'hvac',
        'security',
        'maintenance',
        'construction',
        'other',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getTotalAmountAttribute()
    {
        return $this->price + $this->platform_fee;
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('location_city', 'like', "%{$city}%");
    }
}
