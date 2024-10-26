<?php
namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use App\Services\CoinGeckoApiService;
use App\Services\Interfaces\CacheServiceInterface;
use App\Services\Interfaces\HttpClientInterface;
use Mockery;

class CoinGeckoApiServiceTest extends TestCase
{
    use WithFaker;

    private $service;
    private $httpClientMock;
    private $cacheServiceMock;
    private $apiKey;
    private $coin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = $this->makeFaker();
        $this->apiKey = $this->faker->uuid;
        $this->coin = $this->faker->word;

        $this->httpClientMock = Mockery::mock(HttpClientInterface::class);
        $this->cacheServiceMock = Mockery::mock(CacheServiceInterface::class);
        $this->service = new CoinGeckoApiService($this->httpClientMock, $this->cacheServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testItFetchesHistoricalPrices()
    {
        $days = $this->faker->numberBetween(1, 365);
        $url = "https://api.coingecko.com/api/v3/coins/{$this->coin}/market_chart?vs_currency=usd&days={$days}&interval=daily";

        $this->httpClientMock
            ->shouldReceive('get')
            ->once()
            ->with($url, $this->headers())
            ->andReturn(['prices' => [[1609459200000, 29000]]]);

        $response = $this->service->fetchHistoricalPrices($this->coin, $days, $this->apiKey);

        $this->assertEquals(['prices' => [[1609459200000, 29000]]], $response);
    }

    public function testItFetchesPricesForDateRange()
    {
        [$from, $to] = $this->dateRange();

        $this->cacheServiceMock
            ->shouldReceive('exists')
            ->once()
            ->with($this->coin, \Mockery::type(Carbon::class))
            ->andReturn(false);

        $this->httpClientMock
            ->shouldReceive('get')
            ->once()
            ->andReturn(['prices' => [[1609459200000, 29000]]]);

        $response = $this->service->fetchPriceForRange($this->coin, $from, $to, $this->apiKey);

        $this->assertEquals([[1609459200000, 29000]], $response);
    }

    public function testItFetchesRecentPrices()
    {
        $coins = [$this->faker->word, $this->faker->word];
        $coinsString = implode(',', $coins);
        $precision = 8;  // Set a fixed precision for consistency

        $url = "https://api.coingecko.com/api/v3/simple/price?ids={$coinsString}&vs_currencies=usd&precision={$precision}";

        $this->httpClientMock
            ->shouldReceive('get')
            ->once()
            ->with($url, $this->headers())
            ->andReturn([
                $coins[0] => ['usd' => $this->faker->randomFloat(2, 1000, 50000)],
                $coins[1] => ['usd' => $this->faker->randomFloat(2, 1000, 50000)]
            ]);

        $response = $this->service->fetchRecent($coins, $this->apiKey);

        $this->assertIsArray($response);
    }

    private function headers(): array
    {
        return ['headers' => ['x-cg-demo-api-key' => $this->apiKey]];
    }

    private function dateRange(): array
    {
        $from = Carbon::now()->subDays($this->faker->numberBetween(1, 30))->timestamp;
        $to = Carbon::now()->timestamp;
        return [$from, $to];
    }
}
