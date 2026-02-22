<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\OutreachEmail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with(['company', 'outreachEmails' => function ($q) {
                $q->latest();
            }])
            ->withMax(['followUps as open_follow_up_due_at' => function ($q) {
                $q->where('status', 'open');
            }], 'due_at')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
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

        return view('leads.index', compact('leads', 'cities'));
    }

    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => [
                'required',
                Rule::in(['new', 'demo_ready', 'email_sent', 'call_due', 'won', 'lost', 'postponed']),
            ],
            'notes' => ['nullable', 'string'],
        ]);

        $lead->update($data);

        if (($data['status'] ?? null) === 'email_sent') {
            $this->ensureFollowUp($lead);
        }

        return redirect()->route('leads.index')->with('status', 'Lead durumu guncellendi.');
    }

    public function quickStatus(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => [
                'required',
                Rule::in(['won', 'lost', 'postponed']),
            ],
        ]);

        $lead->update(['status' => $data['status']]);

        return redirect()->route('leads.index')->with('status', 'Hizli durum guncellendi.');
    }

    public function sendEmail(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'to_email' => ['required', 'email', 'max:190'],
            'subject' => ['required', 'string', 'max:255'],
            'body_html' => ['required', 'string'],
        ]);

        $this->applySmtpSettings();

        try {
            Mail::html($data['body_html'], function ($message) use ($data): void {
                $message->to($data['to_email'])
                    ->subject($data['subject']);
            });

            OutreachEmail::create([
                'lead_id' => $lead->id,
                'to_email' => $data['to_email'],
                'subject' => $data['subject'],
                'body_html' => $data['body_html'],
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $lead->update(['status' => 'email_sent']);
            $this->ensureFollowUp($lead);

            return redirect()->route('leads.index')->with('status', 'Mail gonderildi ve 10 gunluk takip gorevi olusturuldu.');
        } catch (\Throwable $e) {
            OutreachEmail::create([
                'lead_id' => $lead->id,
                'to_email' => $data['to_email'],
                'subject' => $data['subject'],
                'body_html' => $data['body_html'],
                'status' => 'failed',
            ]);

            return redirect()->route('leads.index')->with('status', 'Mail gonderimi basarisiz: ' . $e->getMessage());
        }
    }

    private function ensureFollowUp(Lead $lead): void
    {
        $days = (int) (AppSetting::getValue('follow_up_days', env('FOLLOW_UP_DAYS', '10')) ?: 10);
        $dueAt = now()->addDays(max(1, $days));

        FollowUp::query()->updateOrCreate(
            ['lead_id' => $lead->id, 'status' => 'open'],
            ['due_at' => $dueAt]
        );
    }

    private function applySmtpSettings(): void
    {
        $host = AppSetting::getValue('smtp_host');
        $port = AppSetting::getValue('smtp_port');
        $username = AppSetting::getValue('smtp_username');
        $password = AppSetting::getValue('smtp_password');
        $encryption = AppSetting::getValue('smtp_encryption');
        $fromAddress = AppSetting::getValue('smtp_from_address');
        $fromName = AppSetting::getValue('smtp_from_name');

        if (!$host || !$port || !$fromAddress) {
            throw new \RuntimeException('SMTP ayarlari eksik. Ayarlar sayfasini doldurun.');
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', (int) $port);
        Config::set('mail.mailers.smtp.username', $username ?: null);
        Config::set('mail.mailers.smtp.password', $password ?: null);
        Config::set('mail.mailers.smtp.encryption', $encryption ?: null);
        Config::set('mail.from.address', $fromAddress);
        Config::set('mail.from.name', $fromName ?: 'BT Places');
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
                $lead->status,
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
