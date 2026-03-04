@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- Stat Cards --}}
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-7">
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

    {{-- Demo Hazırlandı --}}
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-indigo-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                <i data-lucide="check-check" class="h-5 w-5"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">Demo Hazırlandı</p>
            <p class="mt-1 text-2xl font-bold text-indigo-600">{{ number_format($demoReadyCount) }}</p>
        </div>
    </div>

    {{-- Demo Hazırlanıyor --}}
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-cyan-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-100 text-cyan-600">
                <i data-lucide="loader-circle" class="h-5 w-5"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">Demo Hazırlanıyor</p>
            <p class="mt-1 text-2xl font-bold text-cyan-600">{{ number_format($demoPreparingCount) }}</p>
        </div>
    </div>

    {{-- Arama --}}
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-violet-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                <i data-lucide="phone-call" class="h-5 w-5"></i>
            </div>
            <p class="text-sm font-medium text-gray-500">Arama</p>
            <p class="mt-1 text-2xl font-bold text-violet-600">{{ number_format($calledCount) }}</p>
        </div>
    </div>
</div>

{{-- Quick Overview Chart --}}
<div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="mb-4 text-base font-semibold text-gray-800">Lead Durumu</h3>
        <div class="mx-auto h-[260px] w-full max-h-[260px] sm:h-[290px] sm:max-h-[290px] lg:h-[320px] lg:max-h-[320px] lg:w-full">
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
                $postponedPct = $leadCount > 0 ? round($postponedCount / $leadCount * 100) : 0;
            @endphp
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="text-gray-600">Website'siz Firma Oranı</span>
                    <span class="font-semibold text-amber-600">%{{ $noWebPct }}</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="quick-stat-bar h-full rounded-full bg-gradient-to-r from-amber-400 to-amber-500 transition-[width] duration-1000 ease-out" data-target-width="{{ $noWebPct }}" style="width: 0%"></div>
                </div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="text-gray-600">Lead Dönüşüm Oranı</span>
                    <span class="font-semibold text-emerald-600">%{{ $leadPct }}</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="quick-stat-bar h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-500 transition-[width] duration-1000 ease-out" data-target-width="{{ $leadPct }}" style="width: 0%"></div>
                </div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="text-gray-600">Beklemeye Alınanlar</span>
                    <span class="font-semibold text-violet-600">%{{ $postponedPct }}</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="quick-stat-bar h-full rounded-full bg-gradient-to-r from-violet-400 to-violet-500 transition-[width] duration-1000 ease-out" data-target-width="{{ $postponedPct }}" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function animateQuickStatBars() {
    const bars = document.querySelectorAll('.quick-stat-bar');
    if (!bars.length) return;

    bars.forEach((bar) => {
        bar.style.width = '0%';
    });

    requestAnimationFrame(() => {
        bars.forEach((bar) => {
            const target = Number(bar.getAttribute('data-target-width') || '0');
            const clampedTarget = Math.max(0, Math.min(100, target));
            bar.style.width = `${clampedTarget}%`;
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const leadStatusChartConfig = window.LEAD_STATUS_CHART || {
        labels: ['Arandı', 'Demo Hazırlanıyor', 'Demo Hazırlandı', 'İş alındı', 'İş Alınamadı', 'Beklemeye Alındı'],
        colors: ['#f59e0b', '#06b6d4', '#8b5cf6', '#10b981', '#ef4444', '#94a3b8'],
    };

    const ctx = document.getElementById('leadStatusChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: leadStatusChartConfig.labels,
                datasets: [{
                    data: [{{ $calledCount }}, {{ $demoPreparingCount }}, {{ $demoReadyCount }}, {{ $wonCount }}, {{ $lostCount }}, {{ $postponedCount }}],
                    backgroundColor: leadStatusChartConfig.colors,
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'right', labels: { padding: 16, usePointStyle: true, pointStyleWidth: 12, font: { size: 13 } } }
                }
            }
        });
    }

    animateQuickStatBars();

    lucide.createIcons();
});

window.addEventListener('pageshow', function() {
    animateQuickStatBars();
});
</script>
@endsection
