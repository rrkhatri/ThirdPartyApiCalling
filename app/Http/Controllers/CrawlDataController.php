<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CrawlDataController extends Controller
{
    public function __invoke(Request $request)
    {
        $limit = $request->get('limit', 10);

        try {
            $request->validate([
                'limit' => ['nullable', 'numeric', 'min:1', 'max:20']
            ]);
        } catch (ValidationException $exception) {
            return response()->xml([
                'data' => $exception->errors()
            ], 422);
        }

        return response()->xml([
            'data' => resolve(ApiClient::class)->get(new UserService, $limit)
        ]);
    }
}
