<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'company_id',
        'provider_id',
        'stripe_payment_intent_id',
        'stripe_transfer_id',
        'amount',
        'platform_fee',
        'provider_amount',
        'status',
        'currency',
        'description',
        'paid_at',
        'released_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'provider_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    const STATUSES = [
        'pending',
        'processing',
        'held',
        'released',
        'refunded',
        'failed',
        'disputed',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeHeld($query)
    {
        return $query->where('status', 'held');
    }

    public function scopeReleased($query)
    {
        return $query->where('status', 'released');
    }
}
