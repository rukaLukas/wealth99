<?php
namespace App\Repositories\Interfaces;

use Illuminate\Support\Carbon;

Interface CryptoPriceRepositoryInterface
{
    public function storePricesInBulk(array $insertData): void;

    public function storePrice(string $symbol, float $price, Carbon $timestamp): void;
}