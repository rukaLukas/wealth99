<?php

namespace App\Console;

use Exception;
use App\Models\Coin;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        try {
            Log::info("initializing schedule");
            $schedule->call(function () {
                $coins = Coin::all()->pluck(['coin_id'])->toArray();
                $apiKey = env('COINGECKO_API_KEY');
        
                // Dispatch the job with arguments
                \App\Jobs\FetchRecentCryptoData::dispatch($coins, $apiKey)->onQueue('recent');
            })->name('fetch-recent-crypto-data')
                ->everyMinute()
                ->withoutOverlapping();
        } catch(Exception $e) {
            Log::error($e->getMessage());  
            dump($e->getMessage());          
        }
       
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
