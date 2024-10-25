<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Requests\DateTimeRequest;
use App\Services\Interfaces\CoinPriceServiceInterface;
use Illuminate\Support\Facades\Validator;

class CryptoPriceController extends Controller
{    
    protected $service;

    public function __construct(CoinPriceServiceInterface $service)
    {       
        $this->service = $service;
    }

    public function recent()
    {     
        $prices = $this->service->getRecents();       
        return response($prices, 200);
    }

    public function getPricesByDate(string $datetime)
    {
        $validator = Validator::make(['datetime' => $datetime], [
            'datetime' => 'required|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid datetime format. Expected format: Y-m-d H:i:s'
            ], 400);
        }
              
        $prices = $this->service->getByDate($datetime);

        $statusCode = isset($prices["status_code"]) ? $prices["status_code"] : 200;
        
        return response()->json($prices, $statusCode, [], JSON_UNESCAPED_SLASHES);        
    }
}