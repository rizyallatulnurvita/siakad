<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $fillable = ['kunci', 'nilai'];

    protected static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!array_key_exists($key, static::$cache)) {
            try {
                static::$cache[$key] = static::where('kunci', $key)->value('nilai');
            } catch (\Throwable $e) {
                static::$cache[$key] = null;
            }
        }

        $raw = static::$cache[$key];
        if ($raw === null) {
            return $default;
        }

        $decoded = json_decode($raw, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
    }

    public static function put(string $key, mixed $value): void
    {
        $json = json_encode($value);
        static::updateOrCreate(['kunci' => $key], ['nilai' => $json]);
        static::$cache[$key] = $json;
    }

    public static function forgetCache(): void
    {
        static::$cache = [];
    }
}
