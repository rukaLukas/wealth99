<?php

namespace App\Providers;

use GuzzleHttp\Client;
use App\Services\CacheService;
use App\Services\GuzzleHttpClient;
use App\Services\CoinGeckoApiService;
use Illuminate\Support\ServiceProvider;
use App\Services\Interfaces\HttpClientInterface;
use App\Services\Interfaces\CacheServiceInterface;
use App\Services\Interfaces\CoinGeckoApiServiceInterface;
use App\Repositories\Interfaces\CryptoPriceRepositoryInterface;
use App\Repositories\CryptoPriceRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(HttpClientInterface::class, function () {
            return new GuzzleHttpClient(new Client());
        });
        $this->app->bind(HttpClientInterface::class, GuzzleHttpClient::class);
        $this->app->bind(CoinGeckoApiServiceInterface::class, CoinGeckoApiService::class);
        $this->app->bind(CryptoPriceRepositoryInterface::class, CryptoPriceRepository::class);
        $this->app->bind(CacheServiceInterface::class, CacheService::class);
    }
}
