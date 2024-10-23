<?php
namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;
use App\Services\Interfaces\CacheServiceInterface;

class CacheService implements CacheServiceInterface
{
    public function exists(string $symbol, Carbon $date): bool
    {
        // $key = "{$symbol}:{$date->toDateString()}";
        $key = "{$symbol}:{$date->format('Y-m-d-H:i')}";
        return Redis::exists($key);
    }

    public function store(string $symbol, Carbon $date, array $data): void
    {
        // $key = "{$symbol}:{$date->toDateString()}";
        $key = "{$symbol}:{$date->format('Y-m-d-H:i')}";
        // dd($key, json_encode($data));
        Redis::set($key, json_encode($data));
    }

    /**
     * Retrieve data from Redis.
     *
     * @param string $symbol
     * @param Carbon $timestamp
     * @return array|null
     */
    public function get(string $symbol, Carbon $timestamp): ?array
    {
        $key = "{$symbol}:{$timestamp->format('Y-m-d-H:i')}";
        $data = Redis::get($key);

        return $data ? json_decode($data, true) : null;
    }
}
