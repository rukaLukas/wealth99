<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Coin;
use Illuminate\Support\Carbon;
use App\Services\CoinPriceService;
use App\Jobs\FetchRecentCryptoData;
use Illuminate\Support\Facades\Bus;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Interfaces\CoinPriceServiceInterface;
use App\Repositories\Interfaces\CryptoPriceRepositoryInterface;

class RecentCryptoPricesFeatureTest extends TestCase
{
    // protected $url;
    protected $cacheServiceMock;
    protected $coinPriceService;
    protected $repositoryMock;
    protected function setUp(): void
    {
        parent::setUp();     
        $cacheServiceMock = $this->setupMockServices();    
        $this->cacheServiceMock = $cacheServiceMock;      
        Bus::fake();
        $this->app->instance(\App\Services\Interfaces\CacheServiceInterface::class, $cacheServiceMock);
    }

    /** @test */
    public function it_returns_recent_crypto_prices()
    {
        $response = $this->getJson($this->url);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => ['usd'],
                 ]);
    }

    /** @test */
    public function it_returns_correct_structure_for_recent_crypto_prices()
    {
        $coinIds = Coin::all()->pluck('coin_id')->toArray();
     
        $response = $this->getJson($this->url);
        $response->assertStatus(200);

        foreach ($coinIds as $coinId) {
            $response->assertJsonStructure([
                $coinId => ['usd'],
            ]);
        }
    }

    /** @test */
    public function it_returns_valid_prices_for_each_crypto()
    {
        $response = $this->getJson($this->url);

        $response->assertStatus(200);

        $data = $response->json();

        foreach ($data as $coin => $price) {
            $this->assertArrayHasKey('usd', $price);
            $this->assertGreaterThan(0, $price['usd']);
        }
    }

    /** @test */
    public function it_handles_empty_recent_crypto_prices_gracefully()
    {
        // Mocking the service to return an empty array
        $mockService = new class implements CoinPriceServiceInterface {
            public function getRecents(): array {
                return [];
            }
            public function getByDate($datetime): ?array {
                return [];
            }
        };

        $this->app->instance(CoinPriceServiceInterface::class, $mockService);

        $response = $this->getJson($this->url);

        $response->assertStatus(200)
                ->assertExactJson([]);
    }

    /** @test */
    public function it_uses_cached_recent_prices_or_dispatches_job_if_cache_missing()
    {
        $datetimeKey = 'recent_' . Carbon::now('UTC')->format('Y-m-d-H:i');
        $recentCacheKey = 'recent_prices';
        
        // Mock cache response: cache for current datetime doesn't exist but recent cache does
        $this->cacheServiceMock->shouldReceive('exists')->with($datetimeKey)->andReturn(false);
        $this->cacheServiceMock->shouldReceive('exists')->with($recentCacheKey)->andReturn(true);
        
        $response = $this->getJson($this->url);
        $response->assertStatus(200)
        ->assertExactJson($this->cacheServiceMock->get($recentCacheKey));     
        
        Bus::assertDispatched(FetchRecentCryptoData::class);
    }

    public function setupMockServices()
    {
        $cacheServiceMock = \Mockery::mock(\App\Services\Interfaces\CacheServiceInterface::class);
        $cacheServiceMock->shouldReceive('exists')->andReturn(false);
        // $cacheServiceMock->shouldReceive('get')->andReturn(null);
        $coinIds = Coin::all()->pluck('coin_id')->toArray();

        // Generate mock data for each coin with a random USD price
        $mockPrices = [];
        foreach ($coinIds as $coin) {
            $mockPrices[$coin] = ['usd' => rand(1000, 50000)]; // Random USD price for each coin
        }

        // Configure the cache mock to return this generated data
        $cacheServiceMock->shouldReceive('get')->withAnyArgs()->andReturn($mockPrices);
        $cacheServiceMock->shouldReceive('store')->andReturn(true);

        return $cacheServiceMock;
    }
}