<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertCryptoPricesTableToHypertable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:hypertable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert crypto coins table to Hypertable';

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
            DB::statement('SELECT create_hypertable(\'crypto_prices\', \'last_updated_at\', if_not_exists => TRUE);');
            $this->info('The crypto_prices table has been converted into a hypertable.');
            dump("The crypto_prices table has been converted into a hypertable");
            return 0;
        } catch(Exception $e) {
            $this->error($e->getMessage());
            dump("error");
        }        
    }
}
