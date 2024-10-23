<?php
namespace App\Services\Interfaces;

interface HttpClientInterface
{
    public function get(string $url, array $headers = []): array;
}