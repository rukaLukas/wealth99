<?php
namespace App\Jobs;

use Illuminate\Support\Carbon;
use App\Jobs\AbstractCryptoJob;

use Illuminate\Support\Facades\Log;
use App\Services\Interfaces\CoinGeckoApiServiceInterface;

class FetchRecentCryptoData extends AbstractCryptoJob
{
    public $tries = 3;
    public $retryAfter = 5;
    
    protected function fetchData(CoinGeckoApiServiceInterface $service)
    {        
        Log::info('Getting recent prices');
        return $service->fetchRecent($this->coins, $this->apiKey);
    }

    /**
     * Process and store prices in the database.
     *
     * @param array $prices
     * @param CryptoPriceRepositoryInterface $cryptoPriceRepository
     * @param CacheServiceInterface $cacheService
     * @return void
     */
    protected function processPrices($response, $cryptoPriceRepository, $cacheService)
    {        
        $cacheService->store("recent_prices", null, $response);
        foreach ($response as $id => $coin) { 
            $timestamp = Carbon::now();
            $price = $coin['usd'];
            $coin = $id;
            // Insert price data into the database
            $cryptoPriceRepository->storePrice($coin, $price, $timestamp);
        }
        Log::info('Store recent prices into cache');
    }
}
