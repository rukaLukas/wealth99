<?php
namespace App\Jobs;

use Carbon\Carbon;
use App\Jobs\AbstractCryptoJob;
use App\Services\Interfaces\CoinGeckoApiServiceInterface;

class FetchCoinDataForDate extends AbstractCryptoJob
{
    protected $dateTime;

    public function __construct(array $coins, string $dateTime, string $apiKey)
    {        
        parent::__construct($coins, $apiKey);        
        $this->dateTime = $dateTime;              
    }

    protected function fetchData(CoinGeckoApiServiceInterface $service)
    {
        $from = Carbon::createFromFormat('Y-m-d H:i', $this->dateTime)->timestamp;
        $to = Carbon::createFromFormat('Y-m-d H:i', $this->dateTime)->addMinutes(5)->timestamp;
        $prices = [];                
        foreach ($this->coins as $coin) {  
            $prices[$coin] = $service->fetchPriceForRange($coin, $from, $to, $this->apiKey);
            dump($coin);
        }
        return $prices;        
    }

    /**
     * Process and store prices in the database.
     *
     * @param array $prices
     * @param CryptoPriceRepositoryInterface $cryptoPriceRepository
     * @param CacheServiceInterface $cacheService
     * @return void
     */
    protected function processPrices($prices, $cryptoPriceRepository, $cacheService)
    {        
        // dd(__LINE__, $prices);
        foreach ($prices as $id => $priceData) {  
            // dd($priceData);
            // dd($id, $priceData, $priceData[count($priceData) - 1]); 
            $priceItem = $priceData[count($priceData) - 1];
            $timestamp = Carbon::createFromTimestampMs($priceItem[0]);
            $price = $priceItem[1];

            // Insert price data into the database
            $cryptoPriceRepository->storePrice($id, $price, $timestamp);

            // Cache the price data in Redis
            $cacheService->store($id, $timestamp, ['price' => $price]);
        }
    }
}
