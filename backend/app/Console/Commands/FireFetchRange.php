<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use App\Jobs\FetchCoinDataForDate;
use Carbon\Carbon;

class FireFetchRange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fire:range {--coin=} {--date=}';

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
            $coin = $this->option('coin');
            $date = $this->option('date');            
            // The number of days of historical data you want to fetch            
            // $date = Carbon::createFromDate($date); 
            // dd($date);
            $apiKey = env('COINGECKO_API_KEY');            

            FetchCoinDataForDate::dispatch($coin, $date, $apiKey);            
            return 0;
        } catch(Exception $e) {
            $this->error($e->getMessage());
            dump("error ->>" . $e->getMessage());
        }        
    }
}