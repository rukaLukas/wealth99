<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Coin;
use Illuminate\Support\Carbon;
use App\Jobs\FetchCoinDataForDate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Mockery;

class GetCryptoPricesByDateFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();        
        Bus::fake();
        $this->mockCacheService();
        $this->truncateTables();
    }

    /** @test */
    public function it_returns_prices_for_a_valid_datetime()
    {
        $date = Carbon::now()->subDays(5)->format('Y-m-d H:i:s');
        $this->mockRepositoryToReturnPrices($date);

        $response = $this->getJson("/api/v1/prices/{$date}");

        $response->assertStatus(200)
                 ->assertJsonStructure(['*' => ['symbol', 'price', 'last_updated_at']]);
    }

    /** @test */
    public function it_dispatches_job_and_returns_202_for_unavailable_data()
    {
        Bus::fake();
        $coins = ['bitcoin', 'ethereum'];
        $date = '2024-10-22 10:30:00';
        $apiKey = 'your_test_api_key';

        FetchCoinDataForDate::dispatch($coins, $date, $apiKey);

        Bus::assertDispatched(FetchCoinDataForDate::class, function ($job) use ($coins, $date, $apiKey) {
            return $job->getCoins() === $coins &&
                   $job->getDate() === $date &&
                   $job->getApiKey() === $apiKey;
        });
    }

    /** @test */
    public function it_triggers_job_and_returns_202_when_data_unavailable()
    {
        $date = Carbon::now()->subDays(5)->format('Y-m-d H:i:s');
        $this->mockRepositoryToReturnNoPrices($date);

        $response = $this->getJson("/api/v1/prices/{$date}");

        $response->assertStatus(202)
                 ->assertJson([
                     'message' => 'Request accepted, processing will continue',
                     'status' => 'pending',
                     'resource_url' => "/api/v1/prices/{$date}",
                     'estimated_time_seconds' => 600,
                 ]);
    }

    /** @test */
    public function it_returns_error_for_invalid_datetime_format()
    {
        $invalidDate = '2024-10-99';
        $response = $this->getJson("/api/v1/prices/{$invalidDate}");

        $response->assertStatus(400)
                 ->assertJson(['error' => 'Invalid datetime format. Expected format: Y-m-d H:i:s']);
    }

    private function mockCacheService()
    {
        $cacheServiceMock = Mockery::mock(\App\Services\Interfaces\CacheServiceInterface::class);
        $cacheServiceMock->shouldReceive('exists')->andReturn(false);
        $cacheServiceMock->shouldReceive('get')->andReturn(null);
        $cacheServiceMock->shouldReceive('store')->andReturn(true);
        $this->app->instance(\App\Services\Interfaces\CacheServiceInterface::class, $cacheServiceMock);
    }

    private function truncateTables()
    {
        DB::statement('TRUNCATE TABLE crypto_prices RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE coins RESTART IDENTITY CASCADE');
    }

    private function mockRepositoryToReturnPrices($date)
    {        
        $mockRepo = Mockery::mock(\App\Repositories\Interfaces\CryptoPriceRepositoryInterface::class);
        $mockRepo->shouldReceive('getAllCoins')->andReturn(Coin::all()->pluck('coin_id')->toArray());
        $mockRepo->shouldReceive('getByDate')->withAnyArgs()->andReturn([
            [
                'symbol' => 'bitcoin',
                'price' => 68034.56,
                'last_updated_at' => $date,
            ]
        ]);
        $this->app->instance(\App\Repositories\Interfaces\CryptoPriceRepositoryInterface::class, $mockRepo);
    }

    private function mockRepositoryToReturnNoPrices($date)
    {
        $mockRepo = Mockery::mock(\App\Repositories\Interfaces\CryptoPriceRepositoryInterface::class);
        $mockRepo->shouldReceive('getAllCoins')->andReturn(Coin::all()->pluck('coin_id')->toArray());
        $mockRepo->shouldReceive('getByDate')->withAnyArgs()->andReturn([]);
        $this->app->instance(\App\Repositories\Interfaces\CryptoPriceRepositoryInterface::class, $mockRepo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
