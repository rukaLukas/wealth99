<?php
namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\RequestException;
use App\Services\Interfaces\HttpClientInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    protected $client;
    protected $maxRetries;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->maxRetries = env('MAX_RETRIES_REQUEST', 3);
    }   

    public function get(string $url, array $headers = []): array
    {
        $retryCount = 0;

        while ($retryCount < $this->maxRetries) {
            try {
                // Perform the HTTP GET request
                $response = $this->client->request('GET', $url, ['headers' => $headers]);

                // If the response is successful (HTTP 200 OK)
                if ($response->getStatusCode() === 200) {
                    return json_decode($response->getBody()->getContents(), true);
                }

                // Handle 429 Too Many Requests (rate-limited)
                if ($response->getStatusCode() == 429) {
                    Log::info("Rate limit exceeded. Waiting 1 minute before retrying...");
                    
                    // Wait 1 minute before retrying
                    sleep(65);
                    $retryCount++;
                    continue;
                }

                // If the status code is not successful or 429, throw an exception
                throw new \Exception('Request failed with status code ' . $response->getStatusCode());
            } catch (RequestException $e) {
                // Handle network errors or other request exceptions
                Log::error("HTTP Request failed: " . $e->getMessage());

                // Retry after 1 minute in case of errors or status 429
                sleep(60);
                $retryCount++;
            }
        }

        // After exceeding retries, throw an exception or return an empty array
        Log::error("Max retries exceeded for URL: $url");
        return [];
    }
}
