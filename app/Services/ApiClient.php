<?php

namespace App\Services;

class ApiClient
{
    public function get(BaseService $client, $limit = 10): array
    {
        return $client->get($limit);
    }
}
