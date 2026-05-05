<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class MetaController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'name' => config('app.name'),
            'api_version' => '1.0',
            'documentation' => url('/api/v1/meta'),
        ]);
    }
}
