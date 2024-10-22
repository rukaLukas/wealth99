<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCryptoPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_prices', function (Blueprint $table) {
            $table->string('symbol'); 
            $table->decimal('price', 20, 8);
            $table->timestamp('last_updated_at');
            
            $table->primary(['symbol', 'last_updated_at']);
            
            $table->timestamps();
        });        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crypto_prices');
    }
}
