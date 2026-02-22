<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function getAssetUrl(string $key): string
    {
        $raw = trim((string) static::getValue($key, ''));
        if ($raw === '') {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $raw)) {
            return $raw;
        }

        $path = ltrim($raw, '/');
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        return asset($path);
    }
}
