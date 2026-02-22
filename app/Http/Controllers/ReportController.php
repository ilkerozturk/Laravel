<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\OutreachEmail;

class ReportController extends Controller
{
    public function index()
    {
        $totalLeads = Lead::count();
        $won = Lead::where('status', 'won')->count();
        $lost = Lead::where('status', 'lost')->count();
        $postponed = Lead::where('status', 'postponed')->count();
        $conversion = $totalLeads > 0 ? round(($won / $totalLeads) * 100, 2) : 0;

        $topCities = Company::query()
            ->selectRaw('city, count(*) as total')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $emailStats = [
            'sent' => OutreachEmail::where('status', 'sent')->count(),
            'failed' => OutreachEmail::where('status', 'failed')->count(),
        ];

        $followUpStats = [
            'open' => FollowUp::where('status', 'open')->count(),
            'due' => FollowUp::where('status', 'open')->where('due_at', '<=', now())->count(),
            'done' => FollowUp::where('status', 'done')->count(),
        ];

        return view('reports.index', compact(
            'totalLeads',
            'won',
            'lost',
            'postponed',
            'conversion',
            'topCities',
            'emailStats',
            'followUpStats'
        ));
    }
}
