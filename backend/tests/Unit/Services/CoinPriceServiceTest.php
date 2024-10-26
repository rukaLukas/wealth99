<?php
namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Jobs\FetchCoinDataForDate;
use App\Services\CoinPriceService;
use Illuminate\Support\Facades\Queue;
use App\Services\Interfaces\CacheServiceInterface;
use App\Repositories\Interfaces\CryptoPriceRepositoryInterface;
use Illuminate\Foundation\Testing\WithFaker;

class CoinPriceServiceTest extends TestCase
{
    use WithFaker;    

    public function testGetByDateRetrievesFromDatabaseAndCaches()
    {
        $date = $this->generateDate();

        $formattedDate = $date->toDateTimeString();
        $price = $this->faker->numberBetween(1000, 10000);

        $cacheServiceMock = \Mockery::mock(CacheServiceInterface::class);
        $cacheServiceMock->shouldReceive('exists')
            ->with('bydate', \Mockery::on(function ($arg) use ($formattedDate) {
                return $arg->toDateTimeString() === $formattedDate;
            }))
            ->andReturn(false);
        $cacheServiceMock->shouldReceive('store')->once();

        $repositoryMock = \Mockery::mock(CryptoPriceRepositoryInterface::class);
        $repositoryMock->shouldReceive('getAllCoins')->once()->andReturn(['bitcoin']);
        $repositoryMock->shouldReceive('getByDate')->with(['bitcoin'], $formattedDate)->andReturn([
            'bitcoin' => ['usd' => $price]
        ]);

        $service = new CoinPriceService($cacheServiceMock, $repositoryMock);

        $prices = $service->getByDate($formattedDate);

        $this->assertEquals(['bitcoin' => ['usd' => $price]], $prices);
    }

    public function testGetByDateDispatchesJobIfNotInCacheOrDatabase()
    {
        $date = $this->generateDate();

        $formattedDate = $date->toDateTimeString();

        $cacheServiceMock = \Mockery::mock(CacheServiceInterface::class);
        $cacheServiceMock->shouldReceive('exists')
            ->with('bydate', \Mockery::on(function ($arg) use ($formattedDate) {
                return $arg->toDateTimeString() === $formattedDate;
            }))
            ->andReturn(false);

        $repositoryMock = \Mockery::mock(CryptoPriceRepositoryInterface::class);
        $repositoryMock->shouldReceive('getAllCoins')->andReturn(['bitcoin', 'ethereum']);
        $repositoryMock->shouldReceive('getByDate')->with(['bitcoin', 'ethereum'], $formattedDate)->andReturn([]);

        Queue::fake();
        $service = new CoinPriceService($cacheServiceMock, $repositoryMock);

        $response = $service->getByDate($formattedDate);

        Queue::assertPushed(FetchCoinDataForDate::class);

        $this->assertEquals([
            "status_code" => 202,
            "message" => "Request accepted, processing will continue",
            "status" => "pending",
            "resource_url" => "/api/v1/prices/{$formattedDate}",
            "estimated_time_seconds" => 600
        ], $response);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    private function generateDate()
    {
        return Carbon::now()->subDays(rand(0, 365)) // Random day within the past year
           ->setTime(rand(0, 23), rand(0, 59), rand(0, 59))
           ->setTimezone('UTC'); // Ensure UTC timezone

    }


}
