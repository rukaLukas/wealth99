<?php
namespace App\Services\Interfaces;

interface CoinGeckoApiServiceInterface
{
    public function fetchHistoricalPrices(string $coin, int $days, string $apiKey): array;
    public function convertTimestampToDate(int $timestamp);
}