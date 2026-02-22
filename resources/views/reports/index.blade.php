@extends('layouts.app')
@section('title', 'Raporlar')
@section('page-title', 'Raporlar')

@section('content')
{{-- Lead Stats --}}
<div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
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
            <p class="text-xs font-medium text-gray-500">Kaybedilen</p>
            <p class="mt-0.5 text-xl font-bold text-red-600">{{ number_format($lost) }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-14 w-14 rounded-full bg-amber-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                <i data-lucide="pause-circle" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Ertelenen</p>
            <p class="mt-0.5 text-xl font-bold text-amber-600">{{ number_format($postponed) }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="absolute -right-3 -top-3 h-14 w-14 rounded-full bg-brand-50 opacity-80 group-hover:scale-125"></div>
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-brand-100 text-brand-600">
                <i data-lucide="percent" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Dönüşüm Oranı</p>
            <p class="mt-0.5 text-xl font-bold text-brand-600">%{{ $conversion }}</p>
        </div>
    </div>
</div>

{{-- Mail & Follow-up Stats --}}
<div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <i data-lucide="send" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Mail Gönderildi</p>
            <p class="mt-0.5 text-xl font-bold text-gray-900">{{ number_format($emailStats['sent']) }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-red-100 text-red-600">
                <i data-lucide="mail-x" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Mail Başarısız</p>
            <p class="mt-0.5 text-xl font-bold text-red-600">{{ number_format($emailStats['failed']) }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                <i data-lucide="phone" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Açık Takip</p>
            <p class="mt-0.5 text-xl font-bold text-gray-900">{{ number_format($followUpStats['open']) }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                <i data-lucide="alarm-clock" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Süresi Gelen</p>
            <p class="mt-0.5 text-xl font-bold text-amber-600">{{ number_format($followUpStats['due']) }}</p>
        </div>
    </div>
    <div class="stat-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="relative">
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <i data-lucide="check-circle-2" class="h-4 w-4"></i>
            </div>
            <p class="text-xs font-medium text-gray-500">Tamamlanan Takip</p>
            <p class="mt-0.5 text-xl font-bold text-emerald-600">{{ number_format($followUpStats['done']) }}</p>
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
        <div class="mx-auto h-[200px] w-full max-h-[200px] sm:h-[220px] sm:max-h-[220px] lg:h-[250px] lg:max-h-[250px] lg:w-3/4">
            <canvas id="leadPieChart" class="h-full w-full" height="250"></canvas>
        </div>
    </div>

    {{-- Conversion Funnel --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="mb-4 flex items-center gap-2 text-base font-semibold text-gray-800">
            <i data-lucide="bar-chart-3" class="h-5 w-5 text-gray-400"></i>
            Dönüşüm Tüneli
        </h3>
        <div class="mx-auto h-[200px] w-full max-h-[200px] sm:h-[220px] sm:max-h-[220px] lg:h-[250px] lg:max-h-[250px] lg:w-3/4">
            <canvas id="funnelChart" class="h-full w-full" height="250"></canvas>
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
document.addEventListener('DOMContentLoaded', function() {
    // Lead Status Pie
    const pieCtx = document.getElementById('leadPieChart');
    if (pieCtx) {
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Kazanılan', 'Kaybedilen', 'Ertelenen', 'Diğer'],
                datasets: [{
                    data: [{{ $won }}, {{ $lost }}, {{ $postponed }}, {{ max($totalLeads - $won - $lost - $postponed, 0) }}],
                    backgroundColor: ['#10b981', '#ef4444', '#f59e0b', '#6366f1'],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyleWidth: 10, font: { size: 12 } } }
                }
            }
        });
    }

    // Funnel Bar
    const funnelCtx = document.getElementById('funnelChart');
    if (funnelCtx) {
        new Chart(funnelCtx, {
            type: 'bar',
            data: {
                labels: ['Toplam Lead', 'Mail Gönderildi', 'Takip Yapıldı', 'Kazanılan'],
                datasets: [{
                    data: [{{ $totalLeads }}, {{ $emailStats['sent'] }}, {{ $followUpStats['done'] }}, {{ $won }}],
                    backgroundColor: ['#818cf8', '#38bdf8', '#f59e0b', '#10b981'],
                    borderRadius: 0,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false } },
                    y: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 12, weight: 500 } } }
                }
            }
        });
    }

    lucide.createIcons();
});
</script>
@endsection