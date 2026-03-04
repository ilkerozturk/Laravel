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
                @foreach($statusOptions as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" {{ request('status') === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
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
                    <th class="px-4 py-3">Telefon</th>
                    <th class="px-4 py-3">E-posta</th>
                    <th class="px-4 py-3">Durum</th>
                    <th class="px-4 py-3 min-w-[220px]">NOT</th>
                    <th class="px-4 py-3 min-w-[250px]">Güncelle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @forelse($leads as $lead)
                <tr class="group">
                    <td class="px-4 py-3 w-[190px] max-w-[190px] align-top">
                        <p class="max-w-[170px] truncate font-medium text-gray-900">{{ $lead->company?->name ?: '-' }}</p>
                        <span class="mt-0.5 inline-flex max-w-[170px] items-center gap-1 truncate text-xs text-gray-500">
                            <i data-lucide="map-pin" class="h-3 w-3"></i>
                            {{ $lead->company?->city ?: '-' }} / {{ $lead->company?->district ?: '-' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $lead->company?->phone ?: '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $lead->company?->email ?: '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ \App\Models\Lead::statusBadgeClass($lead->status) }}">{{ \App\Models\Lead::statusLabel($lead->status) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <input form="lead-update-form-{{ $lead->id }}" name="notes" placeholder="Not" value="{{ $lead->notes }}"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                    </td>
                    <td class="px-4 py-3">
                        <form id="lead-update-form-{{ $lead->id }}" method="post" action="{{ route('leads.update-status', $lead) }}" class="flex items-center gap-2">
                            @csrf @method('PATCH')
                            <select name="status"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                                @foreach($statusOptions as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}" {{ $lead->status === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-700">
                                <i data-lucide="save" class="h-3 w-3"></i> Kaydet
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
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