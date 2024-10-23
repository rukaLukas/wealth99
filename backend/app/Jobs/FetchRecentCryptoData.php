<?php
namespace App\Jobs;

use App\Jobs\AbstractCryptoJob;
use Illuminate\Support\Carbon;

use App\Services\Interfaces\CoinGeckoApiServiceInterface;

class FetchRecentCryptoData extends AbstractCryptoJob
{
    protected function fetchData(CoinGeckoApiServiceInterface $service)
    {
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
        foreach ($response as $id => $coin) { 
            $timestamp = Carbon::now();
            $price = $coin['usd'];
            $coin = $id;

            // Insert price data into the database
            $cryptoPriceRepository->storePrice($coin, $price, $timestamp);

            // Cache the price data in Redis
            $cacheService->store($coin, $timestamp, ['price' => $price]);
        }
    }
}
