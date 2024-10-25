<?php
namespace App\Jobs;


use Exception;
use Illuminate\Support\Carbon;
use App\Jobs\AbstractCryptoJob;
use Illuminate\Support\Facades\Log;
use App\Services\Interfaces\CoinGeckoApiServiceInterface;

class FetchHistoricalCryptoData extends AbstractCryptoJob
{
    protected $days;
    public $timeout = 600;

    public function __construct(array $coins, int $days, string $apiKey)
    {
        parent::__construct($coins, $apiKey);
        $this->days = $days;
    }

    protected function fetchData(CoinGeckoApiServiceInterface $service)
    {
        $prices = [];  
        try {
            foreach ($this->coins as $coin) {  
                Log::info("fetching {$this->days} history prices from $coin");
                $prices[$coin] = $service->fetchHistoricalPrices($coin, $this->days, $this->apiKey)['prices'];
                // $this->processPrices2($prices);
                // dd($prices);
            }
            return $prices;
        } catch(Exception $e) {
            Log::error("Failed to get historical price " . $e->getMessage());
        }            
        // return $service->fetchHistoricalPrices($this->coins, $this->days, $this->apiKey);
    }

    protected function processPrices($prices, $cryptoPriceRepository, $cacheService)
    {      
        $results = [];
        foreach ($prices as $symbol => $prices) {
            $transformedPrices = array_map(function($priceData) use ($symbol) {
                return [
                    'symbol' => $symbol,
                    'price' => $priceData[1],
                    'last_updated_at' => Carbon::createFromTimestampMs($priceData[0]), // Convert Unix timestamp in milliseconds
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }, $prices);
        
            // Merge transformed prices into results
            $results = array_merge($results, $transformedPrices);
        }       

        if (!empty($results)) {
            $cryptoPriceRepository->storePricesInBulk($results);

            // Cache the data in Redis
            foreach ($results as $data) {
                $cacheService->store($data['symbol'], $data['last_updated_at'], $data);
            }
        }
    }
}

