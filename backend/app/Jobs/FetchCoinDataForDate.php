<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Interfaces\CacheServiceInterface;
use App\Services\Interfaces\CoinGeckoApiServiceInterface;
use App\Repositories\Interfaces\CryptoPriceRepositoryInterface;

class FetchCoinDataForDate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $coin;
    protected $dateTime; // UTC date/time parameter
    protected $apiKey;

    /**
     * Create a new job instance.
     *
     * @param array $coins List of cryptocurrency IDs to fetch
     * @param Carbon $dateTime Date/Time (UTC) to fetch the historical price for
     * @param string $apiKey CoinGecko API Key
     */
    public function __construct(string $coin, string $dateTime, string $apiKey)
    {
        $this->coin = $coin;
        $this->dateTime = $dateTime;
        $this->apiKey = $apiKey;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
        // Convert the provided date/time to a Unix timestamp
        $from = Carbon::createFromFormat('Y-m-d H:i', $this->dateTime)->timestamp;
        
        // Add two minutes to the provided time for the "to" parameter
        $to = Carbon::createFromFormat('Y-m-d H:i', $this->dateTime)->addMinutes(2)->timestamp;
        
        // Resolve the services
        $coinGeckoApiService = app(CoinGeckoApiServiceInterface::class);
        $cryptoPriceRepository = app(CryptoPriceRepositoryInterface::class);
        $cacheService = app(CacheServiceInterface::class);

        // $timestamp = Carbon::createFromTimestamp($from);
        // if ($cacheService->exists($this->coin, $timestamp)) {
        //     Log::info("Price data for {$this->coin} at {$timestamp} exists in cache. Skipping API call.");
        //     return; // Exit if data is cached
        // }

        try {
            // Make API request to CoinGecko's range endpoint
            $prices = $coinGeckoApiService->fetchPriceForRange($this->coin, $from, $to, $this->apiKey)['prices'];

            if ($prices) {
                $this->processPrices($prices, $cryptoPriceRepository, $cacheService);
            } else {
                Log::error("Failed to fetch valid data for {$this->coin}.");
            }
        } catch (\Exception $e) {
            Log::error("Error fetching data for {$this->coin}: " . $e->getMessage());
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
        foreach ($prices as $priceData) {            
            $timestamp = Carbon::createFromTimestampMs($priceData[0]);
            $price = $priceData[1];
            
            // Check if price data already exists in Redis
            // if ($cacheService->exists($this->coin, $timestamp)) {                
            //     Log::info("Price for {$this->coin} at {$timestamp->toDateTimeString()} already cached.");
            //     continue;
            // }

            // Insert price data into the database
            $cryptoPriceRepository->storePrice($this->coin, $price, $timestamp);

            // Cache the price data in Redis
            $cacheService->store($this->coin, $timestamp, ['price' => $price]);
        }
    }
}