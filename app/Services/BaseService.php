<?php

namespace App\Services;


use Illuminate\Support\Facades\Http;

abstract class BaseService
{
    public ApiClient $client;

    public function __construct()
    {
        $this->client = new ApiClient();
    }

    abstract public function get($limit): array;

    public function fetch($endPoint)
    {
        try {
            $response = Http::get($endPoint);

            if ($response->ok()) {
                return [
                    'data'   => $response->json(),
                    'status' => 200
                ];
            }
        } catch (\Exception $exception) {
            return [
                'data'   => [],
                'status' => $exception->getCode()
            ];
        }
    }
}
