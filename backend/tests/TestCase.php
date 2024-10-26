<?php

namespace Tests;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $url;
    protected function setUp(): void
    {
        parent::setUp(); 
        // dd(env('DB_DATABASE')); 
        // config(['database.connections.pgsql.host' => 'timescaledb_test']);
        // config(['database.connections.pgsql.port' => '5432']);
        if (!Schema::hasTable('migrations')) {
            Artisan::call('key:generate', ['--env' => 'testing']);
            Artisan::call('migrate', ['--env' => 'testing']);
        } 
        $this->url = '/api/v1/prices';
    }
}
