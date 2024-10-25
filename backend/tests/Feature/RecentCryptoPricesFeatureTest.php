<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Coin;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Interfaces\CoinPriceServiceInterface;

class RecentCryptoPricesFeatureTest extends TestCase
{
    // protected $url;
    protected function setUp(): void
    {
        parent::setUp();        
        
        $cacheServiceMock = \Mockery::mock(\App\Services\Interfaces\CacheServiceInterface::class);
        $cacheServiceMock->shouldReceive('exists')->andReturn(false);
        $cacheServiceMock->shouldReceive('get')->andReturn(null);
        $cacheServiceMock->shouldReceive('store')->andReturn(true);
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
}