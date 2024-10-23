<?php
namespace App\Repositories;

use App\Models\CryptoPrice;
use Illuminate\Support\Carbon;
use App\Repositories\Interfaces\CryptoPriceRepositoryInterface;

class CryptoPriceRepository implements CryptoPriceRepositoryInterface
{
    public function storePricesInBulk(array $insertData): void
    {
        CryptoPrice::insert($insertData);
    }

    /**
     * Store price data for a specific coin at a specific timestamp.
     *
     * @param string $symbol Cryptocurrency symbol
     * @param float $price Price of the cryptocurrency
     * @param Carbon $timestamp Timestamp of the price
     * @return void
     */
    public function storePrice(string $symbol, float $price, Carbon $timestamp): void
    {
        // Insert or update the price for the specified coin and timestamp
        CryptoPrice::updateOrCreate(
            [
                'symbol' => $symbol,
                'last_updated_at' => $timestamp,
            ],
            [
                'price' => $price,
            ]
        );
    }
}
