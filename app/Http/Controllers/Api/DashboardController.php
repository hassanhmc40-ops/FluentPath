<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function show(Request $request): DashboardResource
    {
        $data = app(DashboardService::class)->forUser($request->user()->id);

        return new DashboardResource($data);
    }
}
