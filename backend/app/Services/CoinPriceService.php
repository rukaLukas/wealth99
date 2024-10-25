<?php
namespace App\Services;

use Exception;
use App\Models\Coin;
use Illuminate\Support\Carbon;
use App\Jobs\FetchCoinDataForDate;
use Illuminate\Support\Facades\Log;
use App\Services\Interfaces\CacheServiceInterface;
use App\Services\Interfaces\CoinPriceServiceInterface;
use App\Repositories\Interfaces\CryptoPriceRepositoryInterface;

class CoinPriceService implements CoinPriceServiceInterface
{
    protected $cacheService;
    protected $repository;

    public function __construct(CacheServiceInterface $cacheService, CryptoPriceRepositoryInterface $respository)
    {
        $this->cacheService = $cacheService;
        $this->repository = $respository;
    }

    public function getRecents(): array
    {
        $recents = [];               
        if ($this->cacheService->exists('recent_prices')) {
            $recents = $this->cacheService->get('recent_prices');
        }   
        
        return $recents;
    }
    
    public function getByDate(string $date): ?array
    {
        $date = Carbon::parse($date);
        if ($this->cacheService->exists('bydate', $date)) {            
            return $this->cacheService->get('bydate', $date);
        }                 
        $coins = $this->repository->getAllCoins();        
        
        // search this values into database
        $prices = $this->repository->getByDate($coins, $date);
        
        // if not found into database call Job to get this custom data from coinGecko Api            
        if (count($prices) === 0) {                
            $apiKey = env('COINGECKO_API_KEY');
            FetchCoinDataForDate::dispatch($coins, $date, $apiKey);

            return [
                    "status_code" => 202,
                    "message" => "Request accepted, processing will continue",
                    "status"=> "pending",                
                    "resource_url"=> "/api/v1/prices/{$date}",
                    "estimated_time_seconds"=> 600
                ];
        }

        $this->cacheService->store('bydate', $date, $prices);
    
        return $prices;        
    }
}
