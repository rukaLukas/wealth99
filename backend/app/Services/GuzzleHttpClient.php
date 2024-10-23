<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Services\Interfaces\HttpClientInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function get(string $url, array $headers = []): array
    {
        try {
            $response = $this->client->request('GET', $url, [
                'headers' => $headers,
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            throw new \RuntimeException("Error during HTTP request: " . $e->getMessage());
        }
    }
}
