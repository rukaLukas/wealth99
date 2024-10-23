<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Jobs\FetchHistoricalCryptoData;

class FireFetchHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fire:history';

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
            // $coins = [
            //     'bitcoin','bitcoin-cash','litecoin','etherium'
            //     // Add other coins here...
            // ];
            // dd(env('COINS'));
            $coins = [];
            $coins[] = env('COINS');
            dd($coins);
            
            // The number of days of historical data you want to fetch
            $days = 5;
            
            // Your CoinGecko API key
            $apiKey = env('COINGECKO_API_KEY');            

            FetchHistoricalCryptoData::dispatch($coins, $days, $apiKey);
            // dump(__LINE__);
            return 0;
        } catch(Exception $e) {
            $this->error($e->getMessage());
            dump("error ->>" . $e->getMessage());
        }        
    }
}