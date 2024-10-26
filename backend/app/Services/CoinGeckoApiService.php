<?php
namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;
use App\Services\Interfaces\HttpClientInterface;
use App\Services\Interfaces\CacheServiceInterface;
use App\Services\Interfaces\CoinGeckoApiServiceInterface;
use Exception;

class CoinGeckoApiService implements CoinGeckoApiServiceInterface
{
    protected $client;
    protected $url;
    protected $cacheService;
    
    public function __construct(HttpClientInterface $client, CacheServiceInterface $cacheService)
    {
        $this->client = $client;
        $this->cacheService = $cacheService;
        $this->url = "https://api.coingecko.com/api/v3/";
    }

    /**
     * fetchHistoricalPrices function
     *
     * @param string $coin
     * @param integer $days
     * @param string $apiKey
     * @return array
     */
    public function fetchHistoricalPrices(string $coin, int $days, string $apiKey): array
    {
        $url = $this->url . "coins/{$coin}/market_chart?vs_currency=usd&days={$days}&interval=daily";
        $response = $this->makeApiRequest($url, $apiKey);
       
        return $response;        
    }

    public function fetchPriceForRange(string $coin, int $from, int $to, string $apiKey): array
    {
        $attempts = 0;
        $maxAttempts = 3;
        $increment = env('RANGE_TO_INTERVAL', 15); // 15 minutes increment
        $to += $increment * 60;
        $response = "";
        
        while ($attempts < $maxAttempts) {
            $timestamp = Carbon::createFromTimestamp($from);

            if ($this->cacheService->exists($coin, $timestamp)) {
                Log::info("Price data for {$coin} at {$timestamp} exists in cache. Skipping API call.");
                return [];
            }

            $url = $this->url . "coins/{$coin}/market_chart/range?vs_currency=usd&from={$from}&to={$to}";
            $response = $this->makeApiRequest($url, $apiKey);
            
            if (!empty($response['prices'])) {
                echo "has value for date range $coin"; dump($response['prices']);
                return $response['prices'];
                // break;
            }

            // Double the time increment for the next request
            $to += $increment * 60; // Convert minutes to seconds
            $increment *= 2;  // Double the increment value
            $attempts++;
                
            Log::warning("No data found, retrying with a new time range: from {$from} to {$to} (attempt {$attempts})");             
        }  

        Log::error("Failed to retrieve prices after {$maxAttempts} attempts.");
        return [];
    }

    /**
     * fetchRecent function
     *
     * @param string $coin
     * @param string $apiKey
     * @return array
     */
    public function fetchRecent(array $coins, string $apiKey): array
    {        
        $coinsString = implode(',', $coins); 
        $precision = env('COIN_GECKO_PRECISION', 8);
        $url = $this->url . "simple/price?ids={$coinsString}&vs_currencies=usd&precision={$precision}";        
        $response = $this->makeApiRequest($url, $apiKey);        
        
        return $response;
    }

    private function makeApiRequest(string $url, string $apiKey) {        
        try {                
            return $this->client->get($url, [
                'headers' => [
                    'x-cg-demo-api-key' => $apiKey,
                ]
            ]);            
        } catch (\Exception $e) {
            throw new \RuntimeException("Error fetching prices: " . $e->getMessage());
            Log::error($e->getMessage());
        }
    }
}
