<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory;

    public const STATUS_CALLED = 'called';
    public const STATUS_DEMO_PREPARING = 'demo_preparing';
    public const STATUS_DEMO_READY = 'demo_ready';
    public const STATUS_WON = 'won';
    public const STATUS_LOST = 'lost';
    public const STATUS_POSTPONED = 'postponed';

    private const STATUS_LABELS = [
        self::STATUS_CALLED => 'Arandı',
        self::STATUS_DEMO_PREPARING => 'Demo Hazırlanıyor',
        self::STATUS_DEMO_READY => 'Demo Hazırlandı',
        self::STATUS_WON => 'İş alındı',
        self::STATUS_LOST => 'İş Alınamadı',
        self::STATUS_POSTPONED => 'Beklemeye Alındı',
    ];

    private const STATUS_BADGE_CLASSES = [
        self::STATUS_CALLED => 'bg-amber-50 text-amber-700',
        self::STATUS_DEMO_PREPARING => 'bg-cyan-50 text-cyan-700',
        self::STATUS_DEMO_READY => 'bg-violet-50 text-violet-700',
        self::STATUS_WON => 'bg-emerald-50 text-emerald-700',
        self::STATUS_LOST => 'bg-red-50 text-red-700',
        self::STATUS_POSTPONED => 'bg-slate-100 text-slate-700',
    ];

    protected $fillable = [
        'company_id',
        'owner_user_id',
        'status',
        'notes',
    ];

    public static function statusOptions(): array
    {
        return self::STATUS_LABELS;
    }

    public static function statusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? $status;
    }

    public static function statusBadgeClass(string $status): string
    {
        return self::STATUS_BADGE_CLASSES[$status] ?? 'bg-gray-100 text-gray-700';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function outreachEmails(): HasMany
    {
        return $this->hasMany(OutreachEmail::class);
    }
}
