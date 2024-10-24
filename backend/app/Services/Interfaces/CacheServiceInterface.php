<?php
namespace App\Services\Interfaces;

use Illuminate\Support\Carbon;

interface CacheServiceInterface
{
    public function exists(string $symbol, Carbon $date = null): bool;
    public function store(string $symbol, Carbon $date = null, array $data): void;
    public function get(string $symbol, Carbon $timestamp = null): ?array;
}