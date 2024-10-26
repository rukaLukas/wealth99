<?php

namespace Tests\Unit\Jobs;

use Mockery;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Jobs\FetchRecentCryptoData;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\Interfaces\CacheServiceInterface;
use App\Services\Interfaces\CoinGeckoApiServiceInterface;
use App\Repositories\Interfaces\CryptoPriceRepositoryInterface;

class FetchRecentCryptoDataTest extends TestCase
{
    use WithFaker;

    protected $coins;
    protected $apiKey;
    protected $job;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coins = [$this->faker->word, $this->faker->word];
        $this->apiKey = $this->faker->uuid;
        $this->job = new FetchRecentCryptoData($this->coins, $this->apiKey);
    }

    public function testFetchDataCallsServiceFetchRecent()
    {
        $expectedResponse = [$this->coins[0] => ['usd' => $this->faker->randomFloat(2, 12000, 10000)]];
        $serviceMock = $this->mockCoinGeckoService($expectedResponse);
        
        $response = $this->invokeProtectedMethod($this->job, 'fetchData', [$serviceMock]);

        $this->assertEquals($expectedResponse, $response);
    }

    private function mockCoinGeckoService(array $expectedResponse)
    {
        $serviceMock = Mockery::mock(CoinGeckoApiServiceInterface::class);
        $serviceMock->shouldReceive('fetchRecent')
            ->with($this->coins, $this->apiKey)
            ->andReturn($expectedResponse);

        return $serviceMock;
    }

    private function mockCacheService($key, $response)
    {
        $cacheServiceMock = Mockery::mock(CacheServiceInterface::class);
        $cacheServiceMock->shouldReceive('store')
            ->with($key, null, $response)
            ->once();

        return $cacheServiceMock;
    }

    private function mockRepositoryForPrices(array $response)
    {
        $repositoryMock = Mockery::mock(CryptoPriceRepositoryInterface::class);

        foreach ($response as $coin => $data) {
            $repositoryMock->shouldReceive('storePrice')
                ->with($coin, $data['usd'], Mockery::type(Carbon::class))
                ->once();
        }

        return $repositoryMock;
    }

    private function invokeProtectedMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
