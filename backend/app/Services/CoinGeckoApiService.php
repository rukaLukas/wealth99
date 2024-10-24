<?php
namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;
use App\Services\Interfaces\HttpClientInterface;
use App\Services\Interfaces\CacheServiceInterface;
use App\Services\Interfaces\CoinGeckoApiServiceInterface;

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
        // dump($url);
        $response = $this->makeApiRequest($url, $apiKey);
        // dd($response);
        return $response;        
    }

    /**
     * fetchPriceForRange function
     *
     * @param string $coin
     * @param integer $from
     * @param integer $to
     * @param string $apiKey
     * @return array
     */
    public function fetchPriceForRange(string $coin, int $from, int $to, string $apiKey): array
    {
        $timestamp = Carbon::createFromTimestamp($from);
        if ($this->cacheService->exists($coin, $timestamp)) {
            Log::info("Price data for {$coin} at {$timestamp} exists in cache. Skipping API call.");            
            return [];
        }
        $url = $this->url . "coins/{$coin}/market_chart/range?vs_currency=usd&from={$from}&to={$to}";
        $response = $this->makeApiRequest($url, $apiKey);
       
        return $response['prices'];        
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
        $timestamp = Carbon::now();
        $uncachedCoins = [];

        // Check for coins not cached
        foreach ($coins as $coin) {            
            if (!$this->cacheService->exists($coin . '_recent', $timestamp)) {
                $uncachedCoins[] = $coin;  // Add to uncached coins list
            }
        }        

        // If all coins are cached, no need to make an API request
        if (empty($uncachedCoins)) {
            return [];
        }        

        // Make API request with only uncached coins
        $coinsString = implode(',', $uncachedCoins); 
        $precision = env('COIN_GECKO_PRECISION', 8);
        $url = $this->url . "simple/price?ids={$coinsString}&vs_currencies=usd&precision={$precision}";        
        $response = $this->makeApiRequest($url, $apiKey);        
        
        // Store the fetched data in cache
        if ($response) {
            foreach ($response as $id => $coinData) {                
                $this->cacheService->store($id, $timestamp, ['price' => $coinData['usd']]);
            }
        }

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
