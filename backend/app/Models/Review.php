<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'reviewer_id',
        'reviewee_id',
        'provider_id',
        'rating',
        'comment',
        'type',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    const TYPES = [
        'company_to_provider',
        'provider_to_company',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
