<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Coin;
use Illuminate\Support\Carbon;
use App\Jobs\FetchCoinDataForDate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;

class GetCryptoPricesByDateFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();        
        Bus::fake();

        $cacheServiceMock = \Mockery::mock(\App\Services\Interfaces\CacheServiceInterface::class);
        $cacheServiceMock->shouldReceive('exists')->andReturn(false);
        $cacheServiceMock->shouldReceive('get')->andReturn(null);
        $cacheServiceMock->shouldReceive('store')->andReturn(true);
        $this->app->instance(\App\Services\Interfaces\CacheServiceInterface::class, $cacheServiceMock);
        $this->resetDatabase();
    }

    /** @test */
    public function it_returns_prices_for_a_valid_datetime()
    {
        // Create test data
        $date = Carbon::now()->subDays(5)->format('Y-m-d H:i:s');

        // Mock repository to return prices for the date
        $this->mockRepoToReturnPrices($date);
      
        // Make request to the API with the valid date
        $response = $this->getJson("{$this->url}/{$date}");

        $response->assertStatus(200)
                    ->assertJsonStructure([
                        '*' => ['symbol', 'price', 'last_updated_at'],
                    ]);
    }

    /** @test */
    public function test_it_dispatches_job_and_returns_202_for_unavailable_data()
    {    
        // Fake the bus to prevent real dispatching
        Bus::fake();

        // Define test data
        $coins = ['bitcoin', 'ethereum'];
        $date = '2024-10-22 10:30:00';
        $apiKey = 'your_test_api_key';

        // Dispatch the job
        FetchCoinDataForDate::dispatch($coins, $date, $apiKey);

        // Assert that the job was dispatched with the correct arguments
        Bus::assertDispatched(FetchCoinDataForDate::class, function ($job) use ($coins, $date, $apiKey) {
            return $job->getCoins() === $coins &&
                   $job->getDate() === $date &&
                   $job->getApiKey() === $apiKey;
        });
    }

    /** @test */
    public function it_dispatches_job_and_returns_202_for_unavailable_data()
    {
        // Create test data with a valid datetime
        $date = Carbon::now()->subDays(5)->format('Y-m-d H:i:s');

        // Mock repository to return no prices, triggering the job dispatch
        $this->mockRepoToReturnNoPrices($date);

        // Make request to the API
        $response = $this->getJson("{$this->url}/{$date}");

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
        // Invalid datetime format
        $invalidDate = '2024-10-99';

        // Make request to the API with the invalid datetime
        $response = $this->getJson("{$this->url}/{$invalidDate}");

        $response->assertStatus(400)
                    ->assertJson([
                        'error' => 'Invalid datetime format. Expected format: Y-m-d H:i:s',
                    ]);
    }

    protected function migrateFreshUsing()
    {
        $this->artisan('migrate:fresh', [
            '--drop-views' => true, 
        ]);
    }

    public function resetDatabase()
    {
        DB::statement('TRUNCATE TABLE crypto_prices RESTART IDENTITY CASCADE');
        DB::statement('TRUNCATE TABLE coins RESTART IDENTITY CASCADE');      
    }

    private function mockRepoToReturnPrices($date)
    {        
        $mockRepo = \Mockery::mock(\App\Repositories\Interfaces\CryptoPriceRepositoryInterface::class);
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

    private function mockRepoToReturnNoPrices($date)
    {
        $mockRepo = \Mockery::mock(\App\Repositories\Interfaces\CryptoPriceRepositoryInterface::class);
        $mockRepo->shouldReceive('getAllCoins')->andReturn(Coin::all()->pluck('coin_id')->toArray());
        $mockRepo->shouldReceive('getByDate')->withAnyArgs()->andReturn([]);
        $this->app->instance(\App\Repositories\Interfaces\CryptoPriceRepositoryInterface::class, $mockRepo);
    }    

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
