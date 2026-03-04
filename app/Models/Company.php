<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'place_id',
        'name',
        'company_title',
        'tax_office',
        'tax_number',
        'phone',
        'email',
        'address',
        'city',
        'district',
        'website',
        'google_category',
        'activity_area',
        'activity_confidence',
    ];

    public function lead(): HasOne
    {
        return $this->hasOne(Lead::class);
    }
}
