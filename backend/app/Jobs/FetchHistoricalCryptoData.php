<?php
namespace App\Jobs;


use App\Services\Interfaces\CoinGeckoApiServiceInterface;
use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\Log;

use App\Jobs\AbstractCryptoJob;


class FetchHistoricalCryptoData extends AbstractCryptoJob
{
    protected $days;

    public function __construct(array $coins, int $days, string $apiKey)
    {
        parent::__construct($coins, $apiKey);
        $this->days = $days;
    }

    protected function fetchData(CoinGeckoApiServiceInterface $service)
    {
        $prices = [];                
        foreach ($this->coins as $coin) {  
            $prices[$coin] = $service->fetchHistoricalPrices($coin, $this->days, $this->apiKey);
            dd($prices);
        }
        return $prices;
        // return $service->fetchHistoricalPrices($this->coins, $this->days, $this->apiKey);
    }

    protected function processPrices($prices, $cryptoPriceRepository, $cacheService)
    {
        dd($prices);
        $insertData = [];
        foreach ($prices as $id => $priceData) {
            $date = Carbon::createFromTimestampMs($priceData[0]);

            // Check Redis cache
            if ($cacheService->exists($id, $date)) {
                Log::info("Price for {$id} at {$date} already cached.");
                continue;
            }

            // Prepare data for insertion
            $insertData[] = [
                'symbol' => $id,
                'price' => $priceData[1],
                'last_updated_at' => $date,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (!empty($insertData)) {
            $cryptoPriceRepository->storePricesInBulk($insertData);

            // Cache the data in Redis
            foreach ($insertData as $data) {
                $cacheService->store($data['symbol'], $data['last_updated_at'], $data);
            }
        }
    }
}

