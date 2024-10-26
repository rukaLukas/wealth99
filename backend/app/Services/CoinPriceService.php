<?php

namespace App\Services;

use App\Models\Coin;
use Illuminate\Support\Carbon;
use App\Jobs\FetchCoinDataForDate;
use App\Services\Interfaces\CacheServiceInterface;
use App\Services\Interfaces\CoinPriceServiceInterface;
use App\Repositories\Interfaces\CryptoPriceRepositoryInterface;

class CoinPriceService implements CoinPriceServiceInterface
{
    protected $cacheService;
    protected $repository;
    private const CACHE_KEY_RECENT = 'recent_prices';
    private const CACHE_KEY_BY_DATE = 'bydate';

    public function __construct(CacheServiceInterface $cacheService, CryptoPriceRepositoryInterface $repository)
    {
        $this->cacheService = $cacheService;
        $this->repository = $repository;
    }

    public function getRecents(): array
    {
        return $this->cacheService->exists(self::CACHE_KEY_RECENT) 
            ? $this->cacheService->get(self::CACHE_KEY_RECENT) 
            : [];
    }
    
    public function getByDate(string $date): ?array
    {
        $parsedDate = Carbon::parse($date);
        
        if ($this->cacheService->exists(self::CACHE_KEY_BY_DATE, $parsedDate)) {
            return $this->cacheService->get(self::CACHE_KEY_BY_DATE, $parsedDate);
        }
        
        $prices = $this->fetchPricesFromDatabaseOrApi($parsedDate);               

        if ($prices === null) {            
            return $this->dispatchFetchJob($parsedDate);
        }

        $this->cacheService->store(self::CACHE_KEY_BY_DATE, $parsedDate, $prices);

        return $prices;
    }

    private function fetchPricesFromDatabaseOrApi(Carbon $date): ?array
    {
        $coins = $this->repository->getAllCoins();        
        $prices = $this->repository->getByDate($coins, $date);
        
        return count($prices) > 0 ? $prices : null;
    }

    private function dispatchFetchJob(Carbon $date): array
    {
        FetchCoinDataForDate::dispatch($this->repository->getAllCoins(), $date, env('COINGECKO_API_KEY'));

        return [
            "status_code" => 202,
            "message" => "Request accepted, processing will continue",
            "status" => "pending",
            "resource_url" => "/api/v1/prices/{$date}",
            "estimated_time_seconds" => 600,
        ];
    }
}
