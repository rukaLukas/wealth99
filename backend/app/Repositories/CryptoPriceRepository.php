<?php
namespace App\Repositories;

use App\Models\Coin;
use App\Models\CryptoPrice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\CryptoPriceRepositoryInterface;

class CryptoPriceRepository implements CryptoPriceRepositoryInterface
{        
    public function getAllCoins(): array
    {
        return Coin::all()->pluck('coin_id')->toArray();
    }

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

    /**
     * getByDate function
     *
     * @param array $symbols
     * @param string $targetDate
     * @return void
     */
    public function getByDate(array $symbols, string $targetDate)
    {       
        $query = "
                WITH ranked_prices AS (
                    SELECT symbol, price, last_updated_at,
                        ROW_NUMBER() OVER (PARTITION BY symbol ORDER BY ABS(EXTRACT(EPOCH FROM last_updated_at - :target_date::timestamp)) ASC) AS rn
                    FROM crypto_prices
                    WHERE last_updated_at BETWEEN :start_time AND :end_time
                )
                SELECT symbol, price, last_updated_at
                FROM ranked_prices
                WHERE rn = 1;
            ";
           
         // Define the time window (2 hours before and after the target date)
         $targetDateTimestamp = date('Y-m-d H:i:s', strtotime($targetDate));         
         $startTime = date('Y-m-d H:i:s', strtotime($targetDate) - (2 * 60 * 60));
         $endTime = date('Y-m-d H:i:s', strtotime($targetDate) + (2 * 60 * 60));
         return DB::select($query, [
             'target_date' => $targetDateTimestamp,
             'start_time'  => $startTime,
             'end_time'    => $endTime,
            //  'symbols'     => implode(',', $symbols),
         ]);
    }
}
