<?php
namespace App\Jobs;

use Exception;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Interfaces\CacheServiceInterface;
use App\Services\Interfaces\CoinGeckoApiServiceInterface;
use App\Repositories\Interfaces\CryptoPriceRepositoryInterface;

abstract class AbstractCryptoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $coins;
    protected $apiKey;

    public function __construct(array $coins, string $apiKey)
    {
        $this->coins = $coins;
        $this->apiKey = $apiKey;       
    }

    public function handle()
    {       
        // Resolve services
        $cryptoPriceRepository = app(CryptoPriceRepositoryInterface::class);
        $cacheService = app(CacheServiceInterface::class);
        $coinGeckoApiService = app(CoinGeckoApiServiceInterface::class);

        // Fetch prices using the custom logic defined in the subclasses
        $prices = $this->fetchData($coinGeckoApiService);

        if ($prices) {
            $this->processPrices($prices, $cryptoPriceRepository, $cacheService);
            return;
        } else {
            Log::error("Failed to fetch valid data.");
        }            
    }

    abstract protected function fetchData(CoinGeckoApiServiceInterface $service);

    abstract protected function processPrices(array $prices, CryptoPriceRepositoryInterface $cryptoPriceRepository, CacheServiceInterface $cacheService);
    
    // Cache logic shared across all subclasses
    protected function cachePrice(CacheServiceInterface $cacheService, string $symbol, $timestamp, $price)
    {
        $cacheService->store($symbol, $timestamp, ['price' => $price]);
    }
}
