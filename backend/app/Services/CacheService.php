<?php
namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redis;
use App\Services\Interfaces\CacheServiceInterface;

class CacheService implements CacheServiceInterface
{
    public function exists(string $symbol, Carbon $date): bool
    {
        $key = "{$symbol}:{$date->toDateString()}";
        return Redis::exists($key);
    }

    public function store(string $symbol, Carbon $date, array $data): void
    {
        $key = "{$symbol}:{$date->toDateString()}";
        Redis::set($key, json_encode($data));
    }
}
