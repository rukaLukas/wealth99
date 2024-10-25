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
    // public $retryAfter = 65;
    public $timeout = 600;

    public function __construct(array $coins, string $dateTime, string $apiKey)
    {        
        parent::__construct($coins, $apiKey);        
        $this->dateTime = $dateTime;              
    }

    protected function fetchData(CoinGeckoApiServiceInterface $service)
    {
        try {
            Log::info("Getting prices by date range");
            $from = Carbon::createFromFormat('Y-m-d H:i:s', $this->dateTime)->timestamp;
            $to = Carbon::createFromFormat('Y-m-d H:i:s', $this->dateTime)->addMinutes(15)->timestamp;
            $prices = [];                
            foreach ($this->coins as $coin) { 
                dump("before call service->fetchPriceForRange $coin");
                $prices[$coin] = $service->fetchPriceForRange($coin, $from, $to, $this->apiKey);
                dump($coin);
            }
            return $prices;
        } catch(Exception $e) {
            Log::error("Fail to get prices by date range " . $e->getMessage());
            dump("Fail to get prices by date range ", $e->getMessage());
            throw $e;
        }                    
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
        foreach ($prices as $id => $priceData) {  
            $priceItem = $priceData[count($priceData) - 1];
            $timestamp = Carbon::createFromTimestampMs($priceItem[0]);
            $price = $priceItem[1];

            // Insert price data into the database
            $cryptoPriceRepository->storePrice($id, $price, $timestamp);

            // Cache the price data in Redis
            $cacheService->store($id, $timestamp, ['price' => $price]);
        }
        Log::info("Store prices by date range into cache");    
    }
}
