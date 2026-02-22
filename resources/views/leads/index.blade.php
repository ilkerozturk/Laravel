@extends('layouts.app')
@section('title', 'Leadler')
@section('page-title', 'Leadler')

@section('content')
{{-- Search & Filter --}}
<div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <form method="get" action="{{ route('leads.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="min-w-[200px] flex-1">
            <label class="mb-1.5 block text-xs font-medium text-gray-500">Ara</label>
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                <input name="q" placeholder="Firma, telefon, e-posta veya not ara" value="{{ request('q') }}"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-medium text-gray-500">İl</label>
            <select name="city"
                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Tüm iller</option>
                @foreach($cities as $city)
                    <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-medium text-gray-500">Durum</label>
            <select name="status"
                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Tüm durumlar</option>
                @foreach(['new','demo_ready','email_sent','call_due','won','lost','postponed'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primary flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                <i data-lucide="filter" class="h-4 w-4"></i>
                Filtrele
            </button>
            <a href="{{ route('leads.index') }}" class="flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
                <i data-lucide="x" class="h-4 w-4"></i>
                Temizle
            </a>
            <a href="{{ route('leads.export-csv', request()->only(['status', 'city'])) }}"
               class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-medium text-emerald-700 hover:bg-emerald-100">
                <i data-lucide="file-spreadsheet" class="h-4 w-4"></i>
                CSV İndir
            </a>
        </div>
    </form>
</div>

{{-- Leads Table --}}
<div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
        <i data-lucide="users" class="h-5 w-5 text-gray-400"></i>
        <h3 class="text-base font-semibold text-gray-800">Lead Listesi</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <th class="px-4 py-3 w-[190px] max-w-[190px]">Firma</th>
                    <th class="px-4 py-3">İletişim</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3">Takip</th>
                    <th class="px-4 py-3">Hızlı Satış</th>
                    <th class="px-4 py-3 min-w-[250px]">Güncelle</th>
                    <th class="px-4 py-3 min-w-[300px]">Mail Gönder</th>
                    <th class="px-4 py-3">Mail Geçmişi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @forelse($leads as $lead)
                @php
                    $statusColors = [
                        'new' => 'bg-blue-50 text-blue-700 ring-blue-200',
                        'demo_ready' => 'bg-violet-50 text-violet-700 ring-violet-200',
                        'email_sent' => 'bg-cyan-50 text-cyan-700 ring-cyan-200',
                        'call_due' => 'bg-amber-50 text-amber-700 ring-amber-200',
                        'won' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                        'lost' => 'bg-red-50 text-red-700 ring-red-200',
                        'postponed' => 'bg-gray-100 text-gray-600 ring-gray-200',
                    ];
                    $cls = $statusColors[$lead->status] ?? 'bg-gray-100 text-gray-600 ring-gray-200';
                @endphp
                <tr class="group">
                    <td class="px-4 py-3 w-[190px] max-w-[190px] align-top">
                        <p class="max-w-[170px] truncate font-medium text-gray-900">{{ $lead->company?->name ?: '-' }}</p>
                        <span class="mt-0.5 inline-flex max-w-[170px] items-center gap-1 truncate text-xs text-gray-500">
                            <i data-lucide="map-pin" class="h-3 w-3"></i>
                            {{ $lead->company?->city ?: '-' }} / {{ $lead->company?->district ?: '-' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="space-y-0.5 text-sm text-gray-600">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="phone" class="h-3 w-3 text-gray-400"></i>
                                {{ $lead->company?->phone ?: '-' }}
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="mail" class="h-3 w-3 text-gray-400"></i>
                                {{ $lead->company?->email ?: '-' }}
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $cls }}">{{ $lead->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        @if($lead->open_follow_up_due_at)
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="calendar-clock" class="h-3.5 w-3.5 text-gray-400"></i>
                                {{ \Illuminate\Support\Carbon::parse($lead->open_follow_up_due_at)->format('Y-m-d H:i') }}
                            </div>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1.5">
                            <form method="post" action="{{ route('leads.quick-status', $lead) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="won">
                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100">
                                    <i data-lucide="check" class="h-3 w-3"></i> Kazanıldı
                                </button>
                            </form>
                            <form method="post" action="{{ route('leads.quick-status', $lead) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="lost">
                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 hover:bg-red-100">
                                    <i data-lucide="x" class="h-3 w-3"></i> Kaybedildi
                                </button>
                            </form>
                            <form method="post" action="{{ route('leads.quick-status', $lead) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="postponed">
                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-gray-100 px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-200">
                                    <i data-lucide="clock" class="h-3 w-3"></i> Ertelendi
                                </button>
                            </form>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <form method="post" action="{{ route('leads.update-status', $lead) }}" class="space-y-2">
                            @csrf @method('PATCH')
                            <select name="status"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                                @foreach(['new','demo_ready','email_sent','call_due','won','lost','postponed'] as $status)
                                    <option value="{{ $status }}" {{ $lead->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                            <input name="notes" placeholder="Not" value="{{ $lead->notes }}"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                            <button type="submit" class="flex items-center gap-1.5 rounded-lg bg-gray-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">
                                <i data-lucide="save" class="h-3 w-3"></i> Kaydet
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-3">
                        <form method="post" action="{{ route('leads.send-email', $lead) }}" class="space-y-2">
                            @csrf
                            <input name="to_email" placeholder="Alıcı e-posta" value="{{ $lead->company?->email }}"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                            <input name="subject" placeholder="Konu" value="Size özel web sitesi demo hazırladık"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                            <textarea name="body_html" rows="3" placeholder="HTML içerik"
                                      class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100"><p>Merhaba {{ $lead->company?->name ?? 'yetkili' }},</p><p>Firmanıza özel web sitesi demo çalışması hazırlayabiliriz.</p><p>İletişim: [telefon]</p></textarea>
                            <button type="submit" class="btn-primary flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-700">
                                <i data-lucide="send" class="h-3 w-3"></i> Mail Gönder
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $recentEmails = $lead->outreachEmails->take(3);
                            $mailColorMap = [
                                'sent' => 'bg-emerald-50 text-emerald-700',
                                'failed' => 'bg-red-50 text-red-700',
                                'pending' => 'bg-amber-50 text-amber-700',
                            ];
                        @endphp
                        @if($recentEmails->isEmpty())
                            <span class="text-gray-400">-</span>
                        @else
                            <div class="space-y-2">
                                @foreach($recentEmails as $mail)
                                    <div class="rounded-lg bg-gray-50 p-2">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $mailColorMap[$mail->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $mail->status }}</span>
                                        <p class="mt-0.5 text-xs text-gray-600">{{ $mail->to_email }}</p>
                                        <p class="text-[10px] text-gray-400">{{ optional($mail->sent_at)->format('Y-m-d H:i') ?: '-' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="inbox" class="h-10 w-10 text-gray-300"></i>
                            <p class="text-sm text-gray-500">Henüz kayıt yok.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($leads->hasPages())
        <div class="border-t border-gray-100 px-6 py-4">
            {{ $leads->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>document.addEventListener('DOMContentLoaded', function() { lucide.createIcons(); });</script>
@endsection