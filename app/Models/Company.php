<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'place_id',
        'name',
        'phone',
        'email',
        'address',
        'city',
        'district',
        'website',
        'google_category',
        'activity_area',
        'activity_confidence',
        'demo_prompt',
    ];

    public function lead(): HasOne
    {
        return $this->hasOne(Lead::class);
    }

    public function demoProjects(): HasMany
    {
        return $this->hasMany(DemoProject::class);
    }

    public function latestDemoProject(): HasOne
    {
        return $this->hasOne(DemoProject::class)->latestOfMany();
    }
}
