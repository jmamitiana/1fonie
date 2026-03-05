<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'provider_id',
        'cover_letter',
        'proposed_price',
        'proposed_date',
        'notes',
        'status',
        'reviewed_at',
    ];

    protected $casts = [
        'proposed_price' => 'decimal:2',
        'proposed_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    const STATUSES = [
        'pending',
        'accepted',
        'rejected',
        'withdrawn',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
