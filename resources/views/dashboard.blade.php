@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- Stat Cards --}}
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
    {{-- Toplam Firma --}}
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-brand-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand-600">
                <i data-lucide="building-2" class="h-5 w-5"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">Toplam Firma</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($companyCount) }}</p>
        </div>
    </div>

    {{-- Website Yok --}}
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-amber-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                <i data-lucide="globe" class="h-5 w-5"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">Website Yok</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($noWebsiteCount) }}</p>
        </div>
    </div>

    {{-- Toplam Lead --}}
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-blue-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                <i data-lucide="users" class="h-5 w-5"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">Toplam Lead</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($leadCount) }}</p>
        </div>
    </div>

    {{-- Kazanılan --}}
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-emerald-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <i data-lucide="trophy" class="h-5 w-5"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">Kazanılan</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600">{{ number_format($wonCount) }}</p>
        </div>
    </div>

    {{-- Kaybedilen --}}
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-red-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600">
                <i data-lucide="x-circle" class="h-5 w-5"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">Kaybedilen</p>
            <p class="mt-1 text-2xl font-bold text-red-600">{{ number_format($lostCount) }}</p>
        </div>
    </div>

    {{-- Arama Zamanı Gelen --}}
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-violet-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                <i data-lucide="phone-call" class="h-5 w-5"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">Arama Zamanı</p>
            <p class="mt-1 text-2xl font-bold text-violet-600">{{ number_format($dueFollowUpCount) }}</p>
        </div>
    </div>
</div>

{{-- Quick Overview Chart --}}
<div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">Lead Durumu</h3>
        <div class="mx-auto h-[190px] w-full max-h-[190px] sm:h-[210px] sm:max-h-[210px] lg:h-[220px] lg:max-h-[220px] lg:w-3/4">
            <canvas id="leadStatusChart" class="h-full w-full" height="220"></canvas>
        </div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">Hızlı İstatistikler</h3>
        <div class="space-y-4">
            @php
                $total = max($companyCount, 1);
                $noWebPct = round($noWebsiteCount / $total * 100);
                $leadPct = $leadCount > 0 ? round($wonCount / $leadCount * 100) : 0;
            @endphp
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="text-gray-600">Website'siz Firma Oranı</span>
                    <span class="font-semibold text-amber-600">%{{ $noWebPct }}</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-amber-500" style="width: {{ $noWebPct }}%"></div>
                </div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="text-gray-600">Lead Dönüşüm Oranı</span>
                    <span class="font-semibold text-emerald-600">%{{ $leadPct }}</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-500" style="width: {{ $leadPct }}%"></div>
                </div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="text-gray-600">Bekleyen Takip</span>
                    <span class="font-semibold text-violet-600">{{ $dueFollowUpCount }}</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-gradient-to-r from-violet-400 to-violet-500" style="width: {{ min($dueFollowUpCount * 10, 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('leadStatusChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Kazanılan', 'Kaybedilen', 'Diğer'],
                datasets: [{
                    data: [{{ $wonCount }}, {{ $lostCount }}, {{ max($leadCount - $wonCount - $lostCount, 0) }}],
                    backgroundColor: ['#10b981', '#ef4444', '#6366f1'],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true, pointStyleWidth: 10 } }
                }
            }
        });
    }
    lucide.createIcons();
});
</script>
@endsection
