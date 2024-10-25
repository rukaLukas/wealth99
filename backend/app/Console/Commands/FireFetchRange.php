<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use App\Jobs\FetchCoinDataForDate;
use App\Repositories\Interfaces\CryptoPriceRepositoryInterface;

class FireFetchRange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fire:range {--date=}';

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
            $coins = ['bitcoin', 'bitcoin-cash', 'litecoin', 'ethereum']; 
            $date = $this->option('date'); 
            
            $apiKey = env('COINGECKO_API_KEY');            
            FetchCoinDataForDate::dispatch($coins, $date, $apiKey);            
            return 0;
        } catch(Exception $e) {
            $this->error($e->getMessage());
            dump("error ->>" . $e->getMessage());
        }        
    }
}