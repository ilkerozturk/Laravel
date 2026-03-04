@extends('layouts.app')
@section('title', 'Raporlar')
@section('page-title', 'Raporlar')

@section('content')
{{-- Lead Stats --}}
<div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-7">
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-14 w-14 rounded-full bg-blue-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                <i data-lucide="users" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Toplam Lead</p>
            <p class="mt-0.5 text-xl font-bold text-gray-900">{{ number_format($totalLeads) }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-14 w-14 rounded-full bg-emerald-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <i data-lucide="trophy" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Kazanılan</p>
            <p class="mt-0.5 text-xl font-bold text-emerald-600">{{ number_format($won) }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-14 w-14 rounded-full bg-red-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-red-100 text-red-600">
                <i data-lucide="x-circle" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">İş Alınamadı</p>
            <p class="mt-0.5 text-xl font-bold text-red-600">{{ number_format($lost) }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-14 w-14 rounded-full bg-amber-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                <i data-lucide="phone-call" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Arandı</p>
            <p class="mt-0.5 text-xl font-bold text-amber-600">{{ number_format($called) }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-14 w-14 rounded-full bg-violet-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                <i data-lucide="hammer" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Demo Hazırlandı</p>
            <p class="mt-0.5 text-xl font-bold text-violet-600">{{ number_format($demoReady) }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-14 w-14 rounded-full bg-cyan-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-100 text-cyan-600">
                <i data-lucide="loader-circle" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Demo Hazırlanıyor</p>
            <p class="mt-0.5 text-xl font-bold text-cyan-600">{{ number_format($demoPreparing) }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-14 w-14 rounded-full bg-slate-100 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-200 text-slate-700">
                <i data-lucide="pause-circle" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Beklemeye Alındı</p>
            <p class="mt-0.5 text-xl font-bold text-slate-700">{{ number_format($postponed) }}</p>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
    {{-- Lead Status Pie --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="mb-4 flex items-center gap-2 text-base font-semibold text-gray-800">
            <i data-lucide="pie-chart" class="h-5 w-5 text-gray-400"></i>
            Lead Durumları
        </h3>
        <div class="mx-auto h-[260px] w-full max-h-[260px] sm:h-[290px] sm:max-h-[290px] lg:h-[320px] lg:max-h-[320px] lg:w-full">
            <canvas id="leadPieChart" class="h-full w-full" height="250"></canvas>
        </div>
    </div>

    {{-- Conversion Funnel --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="mb-4 flex items-center gap-2 text-base font-semibold text-gray-800">
            <i data-lucide="bar-chart-3" class="h-5 w-5 text-gray-400"></i>
            Dönüşüm Tüneli
        </h3>
        @php
            $totalLeadsForPct = max((int) $totalLeads, 1);
            $calledPct = (int) round(($called / $totalLeadsForPct) * 100);
            $demoPreparingPct = (int) round(($demoPreparing / $totalLeadsForPct) * 100);
            $demoReadyPct = (int) round(($demoReady / $totalLeadsForPct) * 100);
            $wonPct = (int) round(($won / $totalLeadsForPct) * 100);
        @endphp
        <div class="space-y-4">
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="text-gray-600">Arandı</span>
                    <span class="font-semibold text-amber-600">%{{ $calledPct }} | {{ number_format($called) }}</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="report-funnel-bar h-full rounded-full bg-gradient-to-r from-amber-400 to-amber-500 transition-[width] duration-1000 ease-out" data-target-width="{{ $calledPct }}" style="width: 0%"></div>
                </div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="text-gray-600">Demo Hazırlanıyor</span>
                    <span class="font-semibold text-cyan-600">%{{ $demoPreparingPct }} | {{ number_format($demoPreparing) }}</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="report-funnel-bar h-full rounded-full bg-gradient-to-r from-cyan-400 to-cyan-500 transition-[width] duration-1000 ease-out" data-target-width="{{ $demoPreparingPct }}" style="width: 0%"></div>
                </div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="text-gray-600">Demo Hazırlandı</span>
                    <span class="font-semibold text-violet-600">%{{ $demoReadyPct }} | {{ number_format($demoReady) }}</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="report-funnel-bar h-full rounded-full bg-gradient-to-r from-violet-400 to-violet-500 transition-[width] duration-1000 ease-out" data-target-width="{{ $demoReadyPct }}" style="width: 0%"></div>
                </div>
            </div>
            <div>
                <div class="mb-1 flex justify-between text-sm">
                    <span class="text-gray-600">İş Alındı</span>
                    <span class="font-semibold text-emerald-600">%{{ $wonPct }} | {{ number_format($won) }}</span>
                </div>
                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
                    <div class="report-funnel-bar h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-500 transition-[width] duration-1000 ease-out" data-target-width="{{ $wonPct }}" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- City Distribution --}}
<div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="flex items-center gap-2 border-b border-gray-100 px-6 py-4">
        <i data-lucide="map" class="h-5 w-5 text-gray-400"></i>
        <h3 class="text-base font-semibold text-gray-800">Şehirlere Göre Firma Dağılımı</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <th class="px-6 py-3">Şehir</th>
                    <th class="px-6 py-3">Firma Sayısı</th>
                    <th class="px-6 py-3">Dağılım</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @php $maxCity = $topCities->max('total') ?: 1; @endphp
            @forelse($topCities as $row)
                <tr class="group">
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="map-pin" class="h-4 w-4 text-gray-400"></i>
                            <span class="font-medium text-gray-900">{{ $row->city }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-3">
                        <span class="text-base font-semibold text-gray-900">{{ number_format($row->total) }}</span>
                    </td>
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-3">
                            <div class="h-2 w-full max-w-[200px] overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-brand-400 to-brand-600" style="width: {{ round($row->total / $maxCity * 100) }}%"></div>
                            </div>
                            <span class="text-xs font-medium text-gray-500">{{ round($row->total / $maxCity * 100) }}%</span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="inbox" class="h-10 w-10 text-gray-300"></i>
                            <p class="text-sm text-gray-500">Veri yok.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
function animateReportFunnelBars() {
    const bars = document.querySelectorAll('.report-funnel-bar');
    if (!bars.length) return;

    bars.forEach((bar) => {
        bar.style.width = '0%';
    });

    requestAnimationFrame(() => {
        bars.forEach((bar, index) => {
            const target = Number(bar.getAttribute('data-target-width') || '0');
            const clampedTarget = Math.max(0, Math.min(100, target));
            setTimeout(() => {
                bar.style.width = `${clampedTarget}%`;
            }, index * 120);
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const leadStatusChartConfig = window.LEAD_STATUS_CHART || {
        labels: ['Arandı', 'Demo Hazırlanıyor', 'Demo Hazırlandı', 'İş alındı', 'İş Alınamadı', 'Beklemeye Alındı'],
        colors: ['#f59e0b', '#06b6d4', '#8b5cf6', '#10b981', '#ef4444', '#94a3b8'],
    };

    // Lead Status Pie
    const pieCtx = document.getElementById('leadPieChart');
    if (pieCtx) {
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: leadStatusChartConfig.labels,
                datasets: [{
                    data: [{{ $called }}, {{ $demoPreparing }}, {{ $demoReady }}, {{ $won }}, {{ $lost }}, {{ $postponed }}],
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

    animateReportFunnelBars();

    lucide.createIcons();
});

window.addEventListener('pageshow', function() {
    animateReportFunnelBars();
});
</script>
@endsection