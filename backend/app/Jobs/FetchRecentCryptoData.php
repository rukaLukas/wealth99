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

class FetchRecentCryptoData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $coins;
    protected $dateTime; // UTC date/time parameter
    protected $apiKey;

    /**
     * Create a new job instance.
     *
     * @param array $coins List of cryptocurrency IDs to fetch
     * @param Carbon $dateTime Date/Time (UTC) to fetch the historical price for
     * @param string $apiKey CoinGecko API Key
     */
    public function __construct(array $coins, string $apiKey)
    {
        $this->coins = $coins; //implode(",", $coins);
        $this->apiKey = $apiKey;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
        // Resolve the services
        $coinGeckoApiService = app(CoinGeckoApiServiceInterface::class);
        $cryptoPriceRepository = app(CryptoPriceRepositoryInterface::class);
        $cacheService = app(CacheServiceInterface::class);

        try {
            // Make API request to CoinGecko's range endpoint
            $response = $coinGeckoApiService->fetchRecent($this->coins, $this->apiKey);

            if ($response) {
                $this->processPrices($response, $cryptoPriceRepository, $cacheService);
            } else {
                Log::error("Failed to fetch valid data for {$this->coins}.");
            }
        } catch (\Exception $e) {
            Log::error("Error fetching data for {$this->coins}: " . $e->getMessage());
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