<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\FollowUp;
use App\Models\Lead;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'companyCount' => Company::count(),
            'noWebsiteCount' => Company::whereNull('website')->orWhere('website', '')->count(),
            'leadCount' => Lead::count(),
            'wonCount' => Lead::where('status', 'won')->count(),
            'lostCount' => Lead::where('status', 'lost')->count(),
            'dueFollowUpCount' => FollowUp::where('status', 'open')->where('due_at', '<=', now())->count(),
        ]);
    }
}
