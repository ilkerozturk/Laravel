<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Lead;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'companyCount' => Company::count(),
            'noWebsiteCount' => Company::whereNull('website')->orWhere('website', '')->count(),
            'leadCount' => Lead::count(),
            'wonCount' => Lead::where('status', Lead::STATUS_WON)->count(),
            'demoPreparingCount' => Lead::where('status', Lead::STATUS_DEMO_PREPARING)->count(),
            'demoReadyCount' => Lead::where('status', Lead::STATUS_DEMO_READY)->count(),
            'lostCount' => Lead::where('status', Lead::STATUS_LOST)->count(),
            'calledCount' => Lead::where('status', Lead::STATUS_CALLED)->count(),
            'postponedCount' => Lead::where('status', Lead::STATUS_POSTPONED)->count(),
        ]);
    }
}
