<?php
namespace App\Jobs;

use Exception;
use Illuminate\Support\Carbon;
use App\Jobs\AbstractCryptoJob;
use Illuminate\Support\Facades\Log;
use App\Services\Interfaces\CoinGeckoApiServiceInterface;

class FetchCoinDataForDate extends AbstractCryptoJob
{
    protected $dateTime;
    public $tries = 1;
    public $timeout = 1000;

    public function __construct(array $coins, string $dateTime, string $apiKey)
    {
        parent::__construct($coins, $apiKey);
        $this->dateTime = $dateTime;
    }

    public function getDate(): string
    {
        return $this->dateTime;
    }

    protected function fetchData(CoinGeckoApiServiceInterface $service): array
    {
        Log::info("Fetching prices for date range");

        try {
            $timestampRange = $this->generateTimestampRange($this->dateTime);
            return $this->fetchPricesForCoins($service, $timestampRange);
        } catch (Exception $e) {
            Log::error("Failed to fetch prices: " . $e->getMessage());
            throw $e;
        }
    }

    protected function processPrices(array $prices, $cryptoPriceRepository, $cacheService): void
    {
        foreach ($prices as $coin => $priceData) {
            $latestPrice = end($priceData);
            $timestamp = Carbon::createFromTimestampMs($latestPrice[0]);
            $price = $latestPrice[1];

            $this->storePriceData($cryptoPriceRepository, $cacheService, $coin, $price, $timestamp);
        }
        Log::info("Stored prices in cache");
    }

    private function generateTimestampRange(string $dateTime): array
    {
        $from = Carbon::createFromFormat('Y-m-d H:i:s', $dateTime)->timestamp;
        $to = Carbon::createFromFormat('Y-m-d H:i:s', $dateTime)->addMinutes(15)->timestamp;

        return [$from, $to];
    }

    private function fetchPricesForCoins(CoinGeckoApiServiceInterface $service, array $timestampRange): array
    {
        [$from, $to] = $timestampRange;
        $prices = [];

        foreach ($this->coins as $coin) {
            $prices[$coin] = $service->fetchPriceForRange($coin, $from, $to, $this->apiKey);
        }

        return $prices;
    }

    private function storePriceData($cryptoPriceRepository, $cacheService, string $coin, float $price, Carbon $timestamp): void
    {
        $cryptoPriceRepository->storePrice($coin, $price, $timestamp);
        $cacheService->store($coin, $timestamp, ['price' => $price]);
    }
}