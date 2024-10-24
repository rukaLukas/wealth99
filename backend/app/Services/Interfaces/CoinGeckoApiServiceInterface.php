<?php
namespace App\Services\Interfaces;

interface CoinGeckoApiServiceInterface
{
    public function fetchHistoricalPrices(string $coin, int $days, string $apiKey): array;
    public function fetchPriceForRange(string $coin, int $from, int $to, string $apiKey): array;
    public function fetchRecent(array $coins, string $apiKey): array;    
}