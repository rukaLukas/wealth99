<?php

namespace App\Console;

use App\Models\Coin;
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
        $schedule->call(function () {
            $coins = Coin::all()->pluck(['coin_id'])->toArray();
            $apiKey = env('COINGECKO_API_KEY');            // Fetch API key from .env
    
            // Dispatch the job with arguments
            \App\Jobs\FetchRecentCryptoData::dispatch($coins, $apiKey)->onQueue('default');
        })->name('fetch-recent-crypto-data')
            ->everyMinute()
            ->withoutOverlapping();
       
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
