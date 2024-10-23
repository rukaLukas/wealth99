<?php

use Illuminate\Database\Seeder;
use Database\Seeds\CoinsTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CoinsTableSeeder::class);
    }
}
