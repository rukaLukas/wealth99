<?php
namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;
use App\Services\Interfaces\CacheServiceInterface;

class CacheService implements CacheServiceInterface
{
    public function exists(string $symbol, Carbon $date = null): bool
    {
        $key = $this->getKey($symbol, $date);
        return Redis::exists($key);
    }

    public function store(string $symbol, Carbon $date = null, array $data, ?int $duration = 600): void
    {
        $key = $this->getKey($symbol, $date);
        Redis::set($key, json_encode($data), 'EX', $duration);
    }

    /**
     * Retrieve data from Redis.
     *
     * @param string $symbol
     * @param Carbon $timestamp
     * @return array|null
     */
    public function get(string $symbol, Carbon $timestamp = null): ?array
    {
        $key = $this->getKey($symbol, $timestamp);
        $data = Redis::get($key);

        return $data ? json_decode($data, true) : null;
    }

    private function getKey(string $symbol, Carbon $timestamp = null) 
    {
        return $key = is_null($timestamp) ? "{$symbol}" : "{$symbol}:{$timestamp->format('Y-m-d-H:i')}";
    }
}
