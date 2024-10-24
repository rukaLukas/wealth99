<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\CoinPriceServiceInterface;

class CoinController extends Controller
{    
    protected $service;

    public function __construct(CoinPriceServiceInterface $service)
    {       
        $this->service = $service;
    }

    
    public function recent()
    {     
        $coins = $this->service->getRecents();       
        return response($coins, 200);
    }
}