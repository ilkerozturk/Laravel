@extends('layouts.app')
@section('title', 'Import Logları')
@section('page-title', 'Import Logları')

@section('content')
<div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand-600">
                <i data-lucide="download-cloud" class="h-5 w-5"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-800">Son Import Kayıtları</h3>
        </div>
        <div class="flex items-center gap-2">
            <form method="post" action="{{ route('companies.import-logs.clear') }}" onsubmit="return confirm('Tum import logları silinsin mi? Bu işlem geri alınamaz.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-300 bg-red-100 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-200">
                    <i data-lucide="trash" class="h-4 w-4"></i>
                    Tümünü Temizle
                </button>
            </form>
            <form id="bulk-delete-import-logs-form" method="post" action="{{ route('companies.import-logs.bulk-destroy') }}" onsubmit="return confirm('Seçilen import logları silinsin mi?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-100">
                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                    Seçilenleri Sil
                </button>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <th class="px-4 py-3">
                        <input id="check-all-import-logs" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    </th>
                    <th class="px-4 py-3">Tarih</th>
                    <th class="px-4 py-3">Hedef</th>
                    <th class="px-4 py-3">Sayfa</th>
                    <th class="px-4 py-3">Çekilen</th>
                    <th class="px-4 py-3">Yeni</th>
                    <th class="px-4 py-3">Güncellendi</th>
                    <th class="px-4 py-3">Atlandı</th>
                    <th class="px-4 py-3">Yeni Lead</th>
                    <th class="px-4 py-3">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @forelse($importLogs as $log)
                <tr class="group">
                    <td class="px-4 py-3">
                        <input form="bulk-delete-import-logs-form" class="import-log-checkbox h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" type="checkbox" name="import_log_ids[]" value="{{ $log->id }}">
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="calendar" class="h-3.5 w-3.5 text-gray-400"></i>
                            {{ optional($log->executed_at)->format('Y-m-d H:i') ?: '-' }}
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="h-3.5 w-3.5 text-gray-400"></i>
                            <span class="font-medium text-gray-900">{{ $log->city }} / {{ $log->district }}</span>
                            @if($log->keyword)
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">{{ $log->keyword }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">{{ $log->pages_processed }}/{{ $log->max_pages }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-base font-semibold text-gray-900">{{ $log->fetched_result_count }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                            <i data-lucide="plus" class="h-3 w-3"></i>
                            {{ $log->created_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                            <i data-lucide="refresh-cw" class="h-3 w-3"></i>
                            {{ $log->updated_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">{{ $log->skipped_count }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2.5 py-0.5 text-xs font-semibold text-violet-700">
                            <i data-lucide="user-plus" class="h-3 w-3"></i>
                            {{ $log->new_lead_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <form method="post" action="{{ route('companies.import-logs.destroy', $log) }}" onsubmit="return confirm('Bu import kaydı silinsin mi?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100">
                                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                Sil
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="inbox" class="h-10 w-10 text-gray-300"></i>
                            <p class="text-sm text-gray-500">Henüz import kaydı yok.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($importLogs->hasPages())
        <div class="border-t border-gray-100 px-6 py-4">
            {{ $importLogs->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('check-all-import-logs');
    const checkboxes = document.querySelectorAll('.import-log-checkbox');

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = checkAll.checked;
            });
        });
    }

    lucide.createIcons();
});
</script>
@endsection