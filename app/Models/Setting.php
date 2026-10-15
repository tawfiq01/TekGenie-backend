<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/** NFR-PERF-05: the whole (tiny) table is read on nearly every request, so it's cached as one blob and invalidated on any write. */
class Setting extends Model
{
    public $timestamps = false;

    private const CACHE_KEY = 'settings.all';

    protected $fillable = ['group', 'key', 'value', 'type'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        return self::allCached()->get($group.'.'.$key, $default);
    }

    public static function put(string $group, string $key, mixed $value, string $type = 'string'): void
    {
        static::query()->updateOrCreate(['group' => $group, 'key' => $key], ['value' => $value, 'type' => $type]);
    }

    /** @return Collection<non-falsy-string, string|null> */
    private static function allCached(): Collection
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn () => static::query()->get()->mapWithKeys(
                fn (Setting $setting) => [$setting->group.'.'.$setting->key => $setting->value]
            )
        );
    }
}
