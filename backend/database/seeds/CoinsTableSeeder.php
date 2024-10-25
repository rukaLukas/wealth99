<?php
namespace Database\Seeds;

use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoinsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $coins = [
                ['symbol' => 'BTC', 'coin_id' => 'bitcoin'],
                ['symbol' => 'BCH', 'coin_id' => 'bitcoin-cash'],
                ['symbol' => 'LTC', 'coin_id' => 'litecoin'],
                ['symbol' => 'ETH', 'coin_id' => 'ethereum'],
                ['symbol' => 'DACXI', 'coin_id' => 'dacxi'],
                ['symbol' => 'LINK', 'coin_id' => 'chainlink'],
                ['symbol' => 'USDT', 'coin_id' => 'tether'],
                ['symbol' => 'XLM', 'coin_id' => 'stellar'],
                ['symbol' => 'DOT', 'coin_id' => 'polkadot'],
                ['symbol' => 'ADA', 'coin_id' => 'cardano'],
                ['symbol' => 'SOL', 'coin_id' => 'solana'],
                ['symbol' => 'AVAX', 'coin_id' => 'avalanche-2'],
                ['symbol' => 'LUNC', 'coin_id' => 'terra-luna'],
                ['symbol' => 'MATIC', 'coin_id' => 'matic-network'],
                ['symbol' => 'USDC', 'coin_id' => 'usd-coin'],
                ['symbol' => 'BNB', 'coin_id' => 'binancecoin'],
                ['symbol' => 'XRP', 'coin_id' => 'ripple'],
                ['symbol' => 'UNI', 'coin_id' => 'uniswap'],
                ['symbol' => 'MKR', 'coin_id' => 'maker'],
                ['symbol' => 'BAT', 'coin_id' => 'basic-attention-token'],
                ['symbol' => 'SAND', 'coin_id' => 'the-sandbox'],
                ['symbol' => 'EOS', 'coin_id' => 'eos'],
            ];
    
            DB::table('coins')->insert($coins);
        } catch(Exception $e) {
            Log::error("failed to seed coins table " . $e->getMessage());
            dump("failed to seed coins table " . $e->getMessage());
        }
    }
}
