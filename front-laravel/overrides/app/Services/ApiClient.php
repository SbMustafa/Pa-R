<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiClient
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('API_BASE_URL', 'http://localhost:8080/api');
    }

    public function get(string $path): mixed
    {
        return Http::get("{$this->baseUrl}{$path}")->json();
    }

    public function post(string $path, array $data): mixed
    {
        return Http::post("{$this->baseUrl}{$path}", $data)->json();
    }

    public function put(string $path, array $data): mixed
    {
        return Http::put("{$this->baseUrl}{$path}", $data)->json();
    }

    public function delete(string $path): mixed
    {
        return Http::delete("{$this->baseUrl}{$path}")->json();
    }
}
