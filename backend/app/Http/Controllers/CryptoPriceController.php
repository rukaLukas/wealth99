<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Validators\DateValidatorInterface;
use App\Services\Interfaces\CoinPriceServiceInterface;

class CryptoPriceController extends Controller
{
    protected $coinPriceService;
    protected $statusResolver;
    protected $dateValidator;

    public function __construct(
        CoinPriceServiceInterface $coinPriceService,        
        DateValidatorInterface $dateValidator
    ) {       
        $this->coinPriceService = $coinPriceService;        
        $this->dateValidator = $dateValidator;
    }

    public function recent(): JsonResponse
    {             
        $recentPrices = $this->coinPriceService->getRecents();
        return response()->json($recentPrices, 200);
    }

    public function getPricesByDate($datetime): JsonResponse
    {
        if (!$this->dateValidator->isValid($datetime)) {
            return response()->json([
                'error' => 'Invalid datetime format. Expected format: Y-m-d H:i:s'
            ], 400);
        }

        $prices = $this->coinPriceService->getByDate($datetime);
        return response()->json($prices, $this->getStatusCode($prices), [], JSON_UNESCAPED_SLASHES);
    }

    private function getStatusCode(array $prices): int
    {
        return $prices["status_code"] ?? 200;
    }
}
