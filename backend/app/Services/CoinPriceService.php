<?php
namespace App\Services;

use App\Models\Coin;
use Illuminate\Support\Carbon;
use App\Services\Interfaces\CacheServiceInterface;
use App\Services\Interfaces\CoinPriceServiceInterface;

class CoinPriceService implements CoinPriceServiceInterface
{
    protected $cacheService;

    public function __construct(CacheServiceInterface $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function getRecents(): array
    {
        $recents = [];
        if (!$this->cacheService->exists('list_coins')) {
            $coins = Coin::all()->pluck(['coin_id'])->toArray();
            $this->cacheService->store('list_coins', null, $coins);
        } 
        
        $coins = $this->cacheService->get('list_coins');
        foreach($coins as $coin) {
            if ($this->cacheService->exists($coin . '_recent')) {
                $recents[] = [
                    $coin => $this->cacheService->get($coin . '_recent')
                ];
            }            
        }
        
        return $recents;
    }
    
    public function getByRange(Carbon $date): ?array
    {
        return [];
    }
}
