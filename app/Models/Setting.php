<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $timestamps = false;

    protected $fillable = ['group', 'key', 'value', 'type'];

    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        return static::query()->where('group', $group)->where('key', $key)->value('value') ?? $default;
    }

    public static function put(string $group, string $key, mixed $value, string $type = 'string'): void
    {
        static::query()->updateOrCreate(['group' => $group, 'key' => $key], ['value' => $value, 'type' => $type]);
    }
}
