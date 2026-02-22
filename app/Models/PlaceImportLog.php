<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaceImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'city',
        'district',
        'keyword',
        'max_pages',
        'pages_processed',
        'fetched_result_count',
        'created_count',
        'updated_count',
        'skipped_count',
        'new_lead_count',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];
}
