<?php
namespace App\Services;

use Illuminate\Support\Carbon;
use App\Services\Interfaces\HttpClientInterface;
use App\Services\Interfaces\CoinGeckoApiServiceInterface;

class CoinGeckoApiService implements CoinGeckoApiServiceInterface
{
    protected $client;
    protected $url;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->url = "https://api.coingecko.com/api/v3/";
    }

    public function fetchHistoricalPrices(string $coin, int $days, string $apiKey): array
    {
        $url = $this->url . "coins/{$coin}/market_chart?vs_currency=usd&days={$days}&interval=daily";

        try {
            return $this->client->get($url, [
                'headers' => [
                    'x-cg-demo-api-key' => $apiKey,
                ]
            ]);            
        } catch (\Exception $e) {
            throw new \RuntimeException("Error fetching prices: " . $e->getMessage());
        }
    }

    public function convertTimestampToDate(int $timestamp): Carbon
    {
        return Carbon::createFromTimestampMs($timestamp);
    }
}
