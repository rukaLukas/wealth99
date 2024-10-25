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
        if (!Schema::hasTable('migrations')) {
            Artisan::call('key:generate', ['--env' => 'testing']);
            DB::statement('CREATE DATABASE IF NOT EXISTS test_database');
            Artisan::call('migrate', ['--env' => 'testing']);
        } 
        $this->url = '/api/v1/prices';
    }
}
