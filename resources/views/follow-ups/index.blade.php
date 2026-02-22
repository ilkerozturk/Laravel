@extends('layouts.app')
@section('title', 'Takipler')
@section('page-title', 'Takipler')

@section('content')
{{-- Stats --}}
<div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-14 w-14 rounded-full bg-blue-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                <i data-lucide="clock" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Açık Takip</p>
            <p class="mt-0.5 text-xl font-bold text-gray-900">{{ $stats['open'] }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-14 w-14 rounded-full bg-amber-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                <i data-lucide="calendar" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Bugün</p>
            <p class="mt-0.5 text-xl font-bold text-gray-900">{{ $stats['today'] }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-14 w-14 rounded-full bg-red-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-red-100 text-red-600">
                <i data-lucide="alert-triangle" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Gecikmiş</p>
            <p class="mt-0.5 text-xl font-bold text-red-600">{{ $stats['overdue'] }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-14 w-14 rounded-full bg-violet-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                <i data-lucide="clock-3" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Yaklaşan</p>
            <p class="mt-0.5 text-xl font-bold text-gray-900">{{ $stats['upcoming'] }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-14 w-14 rounded-full bg-emerald-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <i data-lucide="check-circle-2" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Tamamlanan</p>
            <p class="mt-0.5 text-xl font-bold text-emerald-600">{{ $stats['done'] }}</p>
        </div>
    </div>
</div>

{{-- Quick Filters & Filter --}}
<div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('follow-ups.index', ['quick_filter' => 'today']) }}"
           class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-medium {{ ($quickFilter ?? '') === 'today' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            <i data-lucide="calendar" class="h-4 w-4"></i> Bugün
        </a>
        <a href="{{ route('follow-ups.index', ['quick_filter' => 'overdue']) }}"
           class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-medium {{ ($quickFilter ?? '') === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            <i data-lucide="alert-triangle" class="h-4 w-4"></i> Gecikmiş
        </a>
        <a href="{{ route('follow-ups.index', ['quick_filter' => 'upcoming']) }}"
           class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-medium {{ ($quickFilter ?? '') === 'upcoming' ? 'bg-violet-100 text-violet-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            <i data-lucide="clock-3" class="h-4 w-4"></i> Yaklaşan
        </a>
        <a href="{{ route('follow-ups.index') }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-gray-100 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-200">
            <i data-lucide="x" class="h-4 w-4"></i> Temizle
        </a>
    </div>
    <form method="get" action="{{ route('follow-ups.index') }}" class="flex flex-wrap items-end gap-3">
        <input type="hidden" name="quick_filter" value="{{ $quickFilter }}">
        <div>
            <label class="mb-1.5 block text-xs font-medium text-gray-500">Durum</label>
            <select name="status"
                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Tüm durumlar</option>
                @foreach(['open','done','canceled'] as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2 self-end pb-1">
            <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="due_only" value="1" {{ request('due_only') ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                Sadece şu an aranması gerekenler
            </label>
        </div>
        <button type="submit" class="btn-primary flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
            <i data-lucide="filter" class="h-4 w-4"></i>
            Filtrele
        </button>
    </form>
</div>

{{-- Follow-ups Table --}}
<div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
        <i data-lucide="phone-call" class="h-5 w-5 text-gray-400"></i>
        <h3 class="text-base font-semibold text-gray-800">Takip Listesi</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <th class="px-4 py-3">Firma</th>
                    <th class="px-4 py-3">Lead</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3">Arandı</th>
                    <th class="px-4 py-3">Tarih</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Tamamlanma</th>
                    <th class="px-4 py-3">Not</th>
                    <th class="px-4 py-3 min-w-[280px]">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @forelse($followUps as $followUp)
                @php
                    $isOverdue = $followUp->status === 'open' && $followUp->due_at && $followUp->due_at->isPast();
                    $isToday = $followUp->status === 'open' && $followUp->due_at && $followUp->due_at->isToday();
                @endphp
                <tr class="group {{ $isOverdue ? 'bg-red-50/30' : '' }}">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $followUp->lead?->company?->name ?: '-' }}</td>
                    <td class="px-4 py-3">
                        @if($followUp->lead?->status)
                            @php
                                $lColors = ['new'=>'bg-blue-50 text-blue-700','demo_ready'=>'bg-violet-50 text-violet-700','email_sent'=>'bg-cyan-50 text-cyan-700','call_due'=>'bg-amber-50 text-amber-700','won'=>'bg-emerald-50 text-emerald-700','lost'=>'bg-red-50 text-red-700','postponed'=>'bg-gray-100 text-gray-600'];
                                $lCls = $lColors[$followUp->lead->status] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $lCls }}">{{ $followUp->lead->status }}</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $fColors = ['open'=>'bg-blue-50 text-blue-700','done'=>'bg-emerald-50 text-emerald-700','canceled'=>'bg-gray-100 text-gray-600'];
                            $fCls = $fColors[$followUp->status] ?? 'bg-gray-100 text-gray-600';
                        @endphp
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $fCls }}">{{ $followUp->status }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <form method="post" action="{{ route('follow-ups.mark-called', $followUp) }}">
                            @csrf @method('PATCH')
                            <input type="checkbox" name="called" value="1"
                                   {{ $followUp->status === 'done' ? 'checked' : '' }}
                                   onchange="this.form.submit()"
                                   class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        </form>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        @if($followUp->due_at)
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="calendar-clock" class="h-3.5 w-3.5 {{ $isOverdue ? 'text-red-500' : 'text-gray-400' }}"></i>
                                <span class="{{ $isOverdue ? 'font-medium text-red-600' : '' }}">{{ $followUp->due_at->format('Y-m-d H:i') }}</span>
                            </div>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($isOverdue)
                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                <i data-lucide="alert-circle" class="h-3 w-3"></i> Gecikmiş
                            </span>
                        @elseif($isToday)
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700 badge-pulse">
                                <i data-lucide="bell" class="h-3 w-3"></i> Bugün
                            </span>
                        @elseif($followUp->status === 'open')
                            <span class="inline-flex rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-medium text-violet-700">Yaklaşan</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ optional($followUp->completed_at)->format('Y-m-d H:i') ?: '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $followUp->call_note ?: '-' }}</td>
                    <td class="px-4 py-3">
                        <form method="post" action="{{ route('follow-ups.update-status', $followUp) }}" class="flex flex-wrap items-center gap-2">
                            @csrf @method('PATCH')
                            <select name="status"
                                    class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                                @foreach(['open','done','canceled'] as $status)
                                    <option value="{{ $status }}" {{ $followUp->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                            <input name="call_note" value="{{ $followUp->call_note }}" placeholder="Telefon notu"
                                   class="w-32 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                            <button type="submit" class="flex items-center gap-1 rounded-lg bg-gray-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">
                                <i data-lucide="save" class="h-3 w-3"></i> Kaydet
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="inbox" class="h-10 w-10 text-gray-300"></i>
                            <p class="text-sm text-gray-500">Takip kaydı yok.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($followUps->hasPages())
        <div class="border-t border-gray-100 px-6 py-4">
            {{ $followUps->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>document.addEventListener('DOMContentLoaded', function() { lucide.createIcons(); });</script>
@endsection