<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'company_address',
        'company_city',
        'company_country',
        'company_zipcode',
        'company_phone',
        'company_website',
        'company_tax_id',
        'company_latitude',
        'company_longitude',
    ];

    protected $casts = [
        'company_latitude' => 'decimal:8',
        'company_longitude' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function missions()
    {
        return $this->hasMany(Mission::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
