<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Coin;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;
use App\Jobs\FetchHistoricalCryptoData;

class FireFetchHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fire:history {--days=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
            
            // The number of days of historical data you want to fetch            
            $days = $this->option('days') ? (int)$this->option('days') : env('DAYS_HISTORY', 365);         
            
            $apiKey = env('COINGECKO_API_KEY');            

            FetchHistoricalCryptoData::dispatch($coins, $days, $apiKey);            
            return 0;
        } catch(Exception $e) {
            $this->error($e->getMessage());
            dump("error ->>" . $e->getMessage());
        }        
    }
}