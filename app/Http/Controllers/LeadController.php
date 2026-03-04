<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $statusOptions = Lead::statusOptions();

        $query = Lead::with('company')
            ->withMax(['followUps as open_follow_up_due_at' => function ($q) {
                $q->where('status', 'open');
            }], 'due_at')
            ->latest();

        if ($request->filled('status')) {
            $status = (string) $request->string('status');
            if (array_key_exists($status, $statusOptions)) {
                $query->where('status', $status);
            }
        }
        if ($request->filled('city')) {
            $query->whereHas('company', function ($q) use ($request) {
                $q->where('city', $request->string('city'));
            });
        }
        if ($request->filled('q')) {
            $qValue = '%' . $request->string('q') . '%';
            $query->where(function ($q) use ($qValue) {
                $q->where('notes', 'like', $qValue)
                    ->orWhereHas('company', function ($c) use ($qValue) {
                        $c->where('name', 'like', $qValue)
                            ->orWhere('phone', 'like', $qValue)
                            ->orWhere('email', 'like', $qValue);
                    });
            });
        }

        $leads = $query->paginate(20)->withQueryString();
        $cities = Lead::query()
            ->join('companies', 'companies.id', '=', 'leads.company_id')
            ->whereNotNull('companies.city')
            ->where('companies.city', '!=', '')
            ->distinct()
            ->orderBy('companies.city')
            ->pluck('companies.city');

        return view('leads.index', compact('leads', 'cities', 'statusOptions'));
    }

    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $statusOptions = Lead::statusOptions();

        $data = $request->validate([
            'status' => [
                'nullable',
                Rule::in(array_keys($statusOptions)),
            ],
            'notes' => ['nullable', 'string'],
        ]);

        $payload = [
            'notes' => $data['notes'] ?? null,
        ];

        if (array_key_exists('status', $data) && !empty($data['status']) && $data['status'] !== $lead->status) {
            $payload['status'] = $data['status'];
        }

        $lead->update($payload);

        return redirect()->route('leads.index')->with('status', 'Lead durumu guncellendi.');
    }

    public function exportCsv(Request $request)
    {
        $query = Lead::with('company')->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('city')) {
            $query->whereHas('company', function ($q) use ($request) {
                $q->where('city', $request->string('city'));
            });
        }
        $leads = $query->get();

        $lines = [];
        $lines[] = 'Firma,Telefon,Eposta,Il,Ilce,Durum,Not,Takip Tarihi';
        foreach ($leads as $lead) {
            $dueAt = $lead->followUps()->where('status', 'open')->max('due_at');
            $lines[] = $this->csvRow([
                $lead->company?->name,
                $lead->company?->phone,
                $lead->company?->email,
                $lead->company?->city,
                $lead->company?->district,
                Lead::statusLabel($lead->status),
                $lead->notes,
                $dueAt,
            ]);
        }

        $filename = 'leads-' . now()->format('Ymd-His') . '.csv';
        return Response::make(implode("\n", $lines), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function csvRow(array $values): string
    {
        return implode(',', array_map(static function ($value): string {
            $clean = str_replace('"', '""', (string) ($value ?? ''));
            return '"' . $clean . '"';
        }, $values));
    }
}
