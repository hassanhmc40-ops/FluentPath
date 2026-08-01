<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->user()->isAdmin()) {
            return view('dashboard', ['data' => null]);
        }

        $data = app(DashboardService::class)->forUser($request->user()->id);

        return view('dashboard', compact('data'));
    }
}
