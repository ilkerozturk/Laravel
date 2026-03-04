<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Lead;

class ReportController extends Controller
{
    public function index()
    {
        $totalLeads = Lead::count();
        $won = Lead::where('status', Lead::STATUS_WON)->count();
        $lost = Lead::where('status', Lead::STATUS_LOST)->count();
        $demoPreparing = Lead::where('status', Lead::STATUS_DEMO_PREPARING)->count();
        $demoReady = Lead::where('status', Lead::STATUS_DEMO_READY)->count();
        $called = Lead::where('status', Lead::STATUS_CALLED)->count();
        $postponed = Lead::where('status', Lead::STATUS_POSTPONED)->count();

        $topCities = Company::query()
            ->selectRaw('city, count(*) as total')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('reports.index', compact(
            'totalLeads',
            'won',
            'lost',
            'demoPreparing',
            'demoReady',
            'called',
            'postponed',
            'topCities'
        ));
    }
}
