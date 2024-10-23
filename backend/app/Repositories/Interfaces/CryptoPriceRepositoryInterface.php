<?php
namespace App\Repositories\Interfaces;

Interface CryptoPriceRepositoryInterface
{
    public function storePricesInBulk(array $insertData): void;
}