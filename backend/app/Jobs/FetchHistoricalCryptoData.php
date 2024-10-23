<?php
namespace App\Jobs;

use App\Repositories\Interfaces\CryptoPriceRepositoryInterface;
use App\Services\Interfaces\CoinGeckoApiServiceInterface;
use App\Services\Interfaces\CacheServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchHistoricalCryptoData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $coins;
    protected $days;
    protected $apiKey;
    protected $coinGeckoApiService;
    protected $cryptoPriceRepository;
    protected $cacheService;

    /**
     * Create a new job instance.
     */
    public function __construct(
        array $coins, 
        int $days, 
        string $apiKey
        // CoinGeckoApiServiceInterface $coinGeckoApiService,
        // CryptoPriceRepositoryInterface $cryptoPriceRepository,
        // CacheServiceInterface $cacheService
    ) {        
        $this->coins = $coins;
        $this->days = $days;
        $this->apiKey = $apiKey;        
        // $this->coinGeckoApiService = $coinGeckoApiService;        
        // $this->cryptoPriceRepository = $cryptoPriceRepository;        
        // $this->cacheService = $cacheService;        
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->coinGeckoApiService = app(CoinGeckoApiServiceInterface::class);
        $this->cryptoPriceRepository = app(CryptoPriceRepositoryInterface::class);
        $this->cacheService = app(CacheServiceInterface::class);
        foreach ($this->coins as $coin) {
            try {
                // Fetch historical data from the CoinGecko API
                $prices = $this->coinGeckoApiService->fetchHistoricalPrices($coin, $this->days, $this->apiKey)['prices'];
                
                if ($prices) {
                    $this->processPrices($coin, $prices);
                } else {
                    Log::error("Failed to fetch valid data for {$coin}.");
                }
            } catch (\Exception $e) {
                Log::error("Error fetching data for {$coin}: " . $e->getMessage());
            }
        }
    }

    /**
     * Process and store prices.
     */
    protected function processPrices(string $coin, array $prices)
    {
        $insertData = [];
        foreach ($prices as $key => $priceData) {
            $date = $this->coinGeckoApiService->convertTimestampToDate($priceData[0]);

            // Check Redis cache
            if ($this->cacheService->exists($coin, $date)) {
                Log::info("Price for {$coin} at {$date} already cached.");
                continue;
            }

            // Prepare data for insertion
            $insertData[] = [
                'symbol' => $coin,
                'price' => $priceData[1],
                'last_updated_at' => $date,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (!empty($insertData)) {
            $this->cryptoPriceRepository->storePricesInBulk($insertData);

            // Cache the data in Redis
            foreach ($insertData as $data) {
                $this->cacheService->store($data['symbol'], $data['last_updated_at'], $data);
            }
        }
    }
}
