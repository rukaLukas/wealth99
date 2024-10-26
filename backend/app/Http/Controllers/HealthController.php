<?php

namespace App\Http\Controllers;

class HealthController extends Controller
{
    public function checkHealth() {
        return response()->json(['status' => 'OK']);
    }
}
