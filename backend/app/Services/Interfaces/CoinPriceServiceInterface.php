<?php
namespace App\Services\Interfaces;

use Illuminate\Support\Carbon;

interface CoinPriceServiceInterface
{
    public function getRecents(): array;
    public function getByDate(string $date): ?array;    
}