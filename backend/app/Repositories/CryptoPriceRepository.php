<?php
namespace App\Repositories;

use App\Models\CryptoPrice;
use App\Repositories\Interfaces\CryptoPriceRepositoryInterface;

class CryptoPriceRepository implements CryptoPriceRepositoryInterface
{
    public function storePricesInBulk(array $insertData): void
    {
        CryptoPrice::insert($insertData);
    }
}
