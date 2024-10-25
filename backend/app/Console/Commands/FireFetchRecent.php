<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Coin;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use App\Jobs\FetchRecentCryptoData;
use Illuminate\Support\Facades\Log;

class FireFetchRecent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fire:recent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {  
            $coins = Coin::all()->pluck(['coin_id'])->toArray();
            $apiKey = env('COINGECKO_API_KEY');            
            FetchRecentCryptoData::dispatch($coins, $apiKey);            
            return 0;
        } catch(Exception $e) {
            Log::error($e->getMessage());
            $this->error($e->getMessage());
            dump("error ->>" . $e->getMessage());
        }        
    }
}