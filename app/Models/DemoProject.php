<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'status',
        'prompt_text',
        'folder_path',
        'zip_path',
        'download_token',
        'progress_percent',
        'error_message',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
