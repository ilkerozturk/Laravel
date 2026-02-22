<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $query = FollowUp::with(['lead.company'])->latest('due_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->boolean('due_only')) {
            $query->where('status', 'open')->where('due_at', '<=', now());
        }

        $quickFilter = (string) $request->query('quick_filter', '');
        if ($quickFilter === 'today') {
            $query->where('status', 'open')->whereDate('due_at', now()->toDateString());
        } elseif ($quickFilter === 'overdue') {
            $query->where('status', 'open')->where('due_at', '<', now());
        } elseif ($quickFilter === 'upcoming') {
            $query->where('status', 'open')->where('due_at', '>', now());
        }

        $followUps = $query->paginate(20)->withQueryString();
        $stats = [
            'open' => FollowUp::where('status', 'open')->count(),
            'today' => FollowUp::where('status', 'open')->whereDate('due_at', now()->toDateString())->count(),
            'overdue' => FollowUp::where('status', 'open')->where('due_at', '<', now())->count(),
            'upcoming' => FollowUp::where('status', 'open')->where('due_at', '>', now())->count(),
            'done' => FollowUp::where('status', 'done')->count(),
        ];

        return view('follow-ups.index', compact('followUps', 'stats', 'quickFilter'));
    }

    public function updateStatus(Request $request, FollowUp $followUp): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'done', 'canceled'])],
            'call_note' => ['nullable', 'string'],
        ]);

        $payload = [
            'status' => $data['status'],
            'call_note' => $data['call_note'] ?? null,
        ];
        if ($data['status'] === 'done') {
            $payload['completed_at'] = now();
        } elseif ($data['status'] !== 'done') {
            $payload['completed_at'] = null;
        }

        $followUp->update($payload);

        return redirect()->route('follow-ups.index')->with('status', 'Takip durumu guncellendi.');
    }

    public function markCalled(Request $request, FollowUp $followUp): RedirectResponse
    {
        $called = $request->boolean('called');

        $followUp->update([
            'status' => $called ? 'done' : 'open',
            'completed_at' => $called ? now() : null,
        ]);

        return redirect()->route('follow-ups.index')->with('status', 'Arama yapildi bilgisi guncellendi.');
    }
}
