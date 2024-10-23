<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchDateCryptoData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $coin;
    protected $dateTime; // UTC date/time parameter
    protected $client;
    protected $apiKey;

    /**
     * Create a new job instance.
     *
     * @param array $coins List of cryptocurrency IDs to fetch
     * @param Carbon $dateTime Date/Time (UTC) to fetch the historical price for
     * @param string $apiKey CoinGecko API Key
     */
    public function __construct($coin, Carbon $dateTime, $apiKey)
    {
        $this->coin = $coin;
        $this->dateTime = $dateTime;
        $this->client = new Client(); // Create Guzzle HTTP client instance
        $this->apiKey = $apiKey; // Store the API key
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // CoinGecko API base URL
        $baseUrl = 'https://api.coingecko.com/api/v3/coins';
        
        // Convert the provided UTC date/time to a UNIX timestamp
        $unixTimestampFrom = $this->dateTime->timestamp;
        $unixTimeStampTo = $this->dateTime->addMinute(5)->timestamp;

        if (isset($this->coin)) {
            // Build the URL to fetch market data for the given date
            $url = "{$baseUrl}/{$this->coin}/market_chart/range?vs_currency=usd&from=" . $unixTimestampFrom . "&to=" . $unixTimeStampTo;

            try {
                // Make the API request using Guzzle with the API key header
                $response = $this->client->request('GET', $url, [
                    'headers' => [
                        'x-cg-demo-api-key' => $this->apiKey,
                    ]
                ]);

                $responseBody = json_decode($response->getBody(), true);

                // Check if the response contains the 'market_data' field
                if (isset($responseBody['prices'])) {
                    $price = $responseBody['prices'][1];
                    $this->storePrice($this->coin, $price, $this->dateTime);
                } else {
                    Log::error("Failed to fetch valid data for {$this->coin}. Response: " . $response->getBody());
                }
            } catch (\Exception $e) {
                // Log the error if request fails
                Log::error("Error fetching data for {$this->coin}: " . $e->getMessage());
            }
        }
    }

    /**
     * Store price data in the database.
     *
     * @param string $symbol Cryptocurrency symbol
     * @param float $price Price in USD
     * @param Carbon $timestamp Timestamp of the price
     */
    protected function storePrice($symbol, $price, $timestamp)
    {
        // CryptoPrice::updateOrCreate(
        //     [
        //         'symbol' => strtoupper($symbol),
        //         'last_updated_at' => $timestamp,
        //     ],
        //     [
        //         'price' => $price,
        //     ]
        // );
    }
}