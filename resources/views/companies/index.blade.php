@extends('layouts.app')
@section('title', 'Firmalar')
@section('page-title', 'Firmalar')

@section('content')
@php
    $leadStatusOptions = \App\Models\Lead::statusOptions();
@endphp
{{-- Google Places Import --}}
<div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="mb-4 flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand-600">
            <i data-lucide="map-pin" class="h-5 w-5"></i>
        </div>
        <h3 class="text-base font-semibold text-gray-800">Google Places'ten Firma Çek</h3>
    </div>
    <form method="post" action="{{ route('companies.import-places') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @csrf
        <div>
            <label class="mb-1.5 block text-xs font-medium text-gray-500">İl</label>
            <input name="city" placeholder="örn. Istanbul" value="{{ old('city') }}" required
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-medium text-gray-500">İlçe</label>
            <input name="district" placeholder="örn. Ümraniye" value="{{ old('district') }}" required
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-medium text-gray-500">Anahtar Kelime</label>
            <input name="keyword" placeholder="örn. hortum, cafe" value="{{ old('keyword') }}"
                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-medium text-gray-500">Sayfa Sayısı</label>
            <select name="max_pages"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="1" {{ old('max_pages', '1') == '1' ? 'selected' : '' }}>1 sayfa (20 sonuç)</option>
                <option value="2" {{ old('max_pages') == '2' ? 'selected' : '' }}>2 sayfa (40 sonuç)</option>
                <option value="3" {{ old('max_pages') == '3' ? 'selected' : '' }}>3 sayfa (60 sonuç)</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="btn-primary flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                <i data-lucide="download-cloud" class="h-4 w-4"></i>
                Google Places Çek
            </button>
        </div>
    </form>
</div>

{{-- Search & Filter --}}
<div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <form method="get" action="{{ route('companies.index') }}" class="flex flex-wrap items-end gap-3">
        <div class="min-w-[200px] flex-1">
            <label class="mb-1.5 block text-xs font-medium text-gray-500">Ara</label>
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                <input name="q" placeholder="Firma, telefon, e-posta ara" value="{{ request('q') }}"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-medium text-gray-500">Faaliyet</label>
            <select name="activity_area"
                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Tüm faaliyetler</option>
                @foreach($activityAreas as $activity)
                    <option value="{{ $activity }}" {{ request('activity_area') === $activity ? 'selected' : '' }}>{{ $activity }}</option>
                @endforeach
            </select>
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
            <label class="mb-1.5 block text-xs font-medium text-gray-500">İlçe</label>
            <select name="district"
                    class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                <option value="">Tüm ilçeler</option>
                @foreach($districts as $district)
                    <option value="{{ $district }}" {{ request('district') === $district ? 'selected' : '' }}>{{ $district }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2 self-end pb-1">
            <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="no_website" value="1" {{ request('no_website') ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                Website yok
            </label>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="btn-primary flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                <i data-lucide="filter" class="h-4 w-4"></i>
                Filtrele
            </button>
            <a href="{{ route('companies.index') }}" class="flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
                <i data-lucide="x" class="h-4 w-4"></i>
                Temizle
            </a>
        </div>
    </form>
</div>

{{-- New Company Form (collapsible) --}}
<div class="mb-6">
    <button onclick="document.getElementById('newCompanyForm').classList.toggle('hidden')"
            class="flex items-center gap-2 rounded-xl border border-dashed border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-600 hover:border-brand-400 hover:text-brand-600">
        <i data-lucide="plus-circle" class="h-5 w-5"></i>
        Yeni Firma Ekle
    </button>
    <div id="newCompanyForm" class="mt-3 hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <form method="post" action="{{ route('companies.store') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">Firma Adı</label>
                <input name="name" placeholder="Firma adı" required
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">Place ID</label>
                <input name="place_id" placeholder="Place ID (opsiyonel)"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">Telefon</label>
                <input name="phone" placeholder="Telefon"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">E-posta</label>
                <input name="email" placeholder="E-posta"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">İl</label>
                <input name="city" placeholder="İl"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">İlçe</label>
                <input name="district" placeholder="İlçe"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">Website</label>
                <input name="website" placeholder="Website URL"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">Kategori</label>
                <input name="google_category" placeholder="Kategori"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">Faaliyet Alanı</label>
                <input name="activity_area" placeholder="Faaliyet alanı"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-primary flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Company Table --}}
<div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4">
        <h3 class="flex items-center gap-2 text-base font-semibold text-gray-800">
            <i data-lucide="list" class="h-5 w-5 text-gray-400"></i>
            Firma Listesi
        </h3>
        <form id="bulk-delete-form" method="post" action="{{ route('companies.bulk-destroy') }}" onsubmit="return confirm('Seçilen firmalar silinsin mi?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-100">
                <i data-lucide="trash-2" class="h-4 w-4"></i>
                Seçilenleri Sil
            </button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <th class="px-4 py-3">
                        <input id="check-all-companies" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    </th>
                    <th class="px-4 py-3 w-[250px] max-w-[250px]">Firma</th>
                    <th class="px-4 py-3">E-posta</th>
                    <th class="px-4 py-3">Telefon</th>
                    <th class="px-4 py-3">Website</th>
                    <th class="px-4 py-3">Faaliyet</th>
                    <th class="px-4 py-3">Google</th>
                    <th class="px-4 py-3">Lead</th>
                    <th class="px-4 py-3">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @forelse($companies as $company)
                <tr class="group" data-company-id="{{ $company->id }}">
                    <td class="px-4 py-3">
                        <input form="bulk-delete-form" class="company-checkbox h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" type="checkbox" name="company_ids[]" value="{{ $company->id }}">
                    </td>
                    <td class="px-4 py-3 w-[250px] max-w-[250px] font-medium text-gray-900 js-company-name">
                        <span class="block max-w-[230px] truncate">{{ $company->name }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 js-company-email">{{ $company->email ?: '-' }}</td>
                    <td class="px-4 py-3 text-gray-600 js-company-phone">{{ $company->phone ?: '-' }}</td>
                    <td class="px-4 py-3 js-company-website">
                        @if($company->website)
                            <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 text-brand-600 hover:text-brand-700 hover:underline">
                                <i data-lucide="external-link" class="h-3 w-3"></i>
                                Website
                            </a>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                                <i data-lucide="alert-triangle" class="h-3 w-3"></i>
                                Yok
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 js-company-activity">
                        @if($company->activity_area)
                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">{{ $company->activity_area }}</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($company->place_id)
                            <a href="https://www.google.com/maps/search/?api=1&query=Google&query_place_id={{ urlencode($company->place_id) }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-700 hover:underline">
                                <i data-lucide="map" class="h-3 w-3"></i>
                                Maps
                            </a>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($company->lead)
                            <span class="js-company-lead inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ \App\Models\Lead::statusBadgeClass($company->lead->status) }}">{{ \App\Models\Lead::statusLabel($company->lead->status) }}</span>
                        @else
                            <span class="js-company-lead text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $editCompanyPayload = json_encode([
                                'name' => $company->name,
                                'company_title' => $company->company_title,
                                'tax_office' => $company->tax_office,
                                'tax_number' => $company->tax_number,
                                'phone' => $company->phone,
                                'email' => $company->email,
                                'address' => $company->address,
                                'city' => $company->city,
                                'district' => $company->district,
                                'website' => $company->website,
                                'google_category' => $company->google_category,
                                'activity_area' => $company->activity_area,
                                'lead_status' => $company->lead?->status,
                            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                        @endphp
                        <div class="flex flex-nowrap items-center justify-center gap-1.5 whitespace-nowrap">
                            <button type="button"
                                    class="edit-company-btn inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-gray-400 hover:bg-brand-50 hover:text-brand-600"
                                    title="Düzenle"
                                    data-id="{{ $company->id }}"
                                    data-company='{{ $editCompanyPayload }}'>
                                <i data-lucide="pencil" class="h-4 w-4"></i>
                            </button>
                            <form class="m-0 inline-flex items-center" method="post" action="{{ route('companies.destroy', $company) }}" onsubmit="return confirm('Bu firmayı silmek istiyor musun?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600" title="Sil">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center">
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

    @if($companies->hasPages())
        <div class="border-t border-gray-100 px-6 py-4">
            {{ $companies->links() }}
        </div>
    @endif
</div>
@endsection

{{-- Edit Company Modal --}}
<div id="editModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div class="absolute left-1/2 top-1/2 w-full max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-2xl border border-gray-100 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand-600">
                    <i data-lucide="building-2" class="h-5 w-5"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-800">Firma Düzenle</h3>
            </div>
            <button onclick="closeEditModal()" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                <i data-lucide="x" class="h-5 w-5"></i>
            </button>
        </div>
        <form id="editForm" method="post" class="p-6">
            @csrf
            @method('PATCH')
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Firma Adı *</label>
                    <input id="edit-name" name="name" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Firma Ünvanı</label>
                    <input id="edit-company_title" name="company_title" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Vergi Dairesi</label>
                    <input id="edit-tax_office" name="tax_office" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Vergi Numarası</label>
                    <input id="edit-tax_number" name="tax_number" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Telefon</label>
                    <input id="edit-phone" name="phone" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">E-posta</label>
                    <input id="edit-email" name="email" type="email" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Adres</label>
                    <input id="edit-address" name="address" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">İl</label>
                    <input id="edit-city" name="city" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">İlçe</label>
                    <input id="edit-district" name="district" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Website</label>
                    <input id="edit-website" name="website" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Google Kategori</label>
                    <input id="edit-google_category" name="google_category" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Faaliyet Alanı</label>
                    <input id="edit-activity_area" name="activity_area" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Lead Durumu</label>
                    <select id="edit-lead_status" name="lead_status" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                        <option value="">Seçiniz</option>
                        @foreach($leadStatusOptions as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" onclick="closeEditModal()" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">İptal</button>
                <button type="submit" class="flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<div id="companies-toast" class="fixed bottom-6 right-6 z-[70] hidden translate-y-2 opacity-0 transition-all duration-300">
    <div id="companies-toast-inner" class="flex items-start gap-3 rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg">
        <span id="companies-toast-message" class="leading-5"></span>
        <button type="button" id="companies-toast-close" class="-mr-1 inline-flex h-5 w-5 items-center justify-center rounded text-white/90 hover:bg-white/20 hover:text-white" aria-label="Kapat">
            <i data-lucide="x" class="h-3.5 w-3.5"></i>
        </button>
    </div>
</div>

@section('scripts')
<script>
const companyUpdateUrlTemplate = '{{ route('companies.update', ['company' => '__COMPANY_ID__']) }}';

function showToast(message, type = 'success') {
    const toast = document.getElementById('companies-toast');
    const toastInner = document.getElementById('companies-toast-inner');
    const toastMessage = document.getElementById('companies-toast-message');
    if (!toast || !toastInner || !toastMessage) return;

    toastInner.className = 'rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg ' +
        (type === 'success' ? 'bg-emerald-600' : 'bg-red-600');
    toastMessage.textContent = message;

    toast.classList.remove('hidden', 'translate-y-2', 'opacity-0');

    clearTimeout(window.__companiesToastTimer);
    window.__companiesToastTimer = setTimeout(() => {
        hideToast();
    }, 2600);
}

function hideToast() {
    const toast = document.getElementById('companies-toast');
    if (!toast) return;
    toast.classList.add('translate-y-2', 'opacity-0');
    setTimeout(() => toast.classList.add('hidden'), 300);
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function updateCompanyRow(company) {
    const row = document.querySelector(`tr[data-company-id="${company.id}"]`);
    if (!row) return;

    const nameCell = row.querySelector('.js-company-name');
    const emailCell = row.querySelector('.js-company-email');
    const phoneCell = row.querySelector('.js-company-phone');
    const websiteCell = row.querySelector('.js-company-website');
    const activityCell = row.querySelector('.js-company-activity');
    const leadCell = row.querySelector('.js-company-lead');

    if (nameCell) {
        nameCell.innerHTML = `<span class="block max-w-[230px] truncate">${escapeHtml(company.name || '-')}</span>`;
    }
    if (emailCell) emailCell.textContent = company.email || '-';
    if (phoneCell) phoneCell.textContent = company.phone || '-';

    if (websiteCell) {
        if (company.website) {
            const safeWebsite = escapeHtml(company.website);
            websiteCell.innerHTML = `
                <a href="${safeWebsite}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-1 text-brand-600 hover:text-brand-700 hover:underline">
                    <i data-lucide="external-link" class="h-3 w-3"></i>
                    Website
                </a>
            `;
        } else {
            websiteCell.innerHTML = `
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                    <i data-lucide="alert-triangle" class="h-3 w-3"></i>
                    Yok
                </span>
            `;
        }
    }

    if (activityCell) {
        if (company.activity_area) {
            activityCell.innerHTML = `<span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">${escapeHtml(company.activity_area)}</span>`;
        } else {
            activityCell.innerHTML = '<span class="text-gray-400">-</span>';
        }
    }

    if (leadCell) {
        if (company.lead_status && company.lead_status_label) {
            leadCell.className = `js-company-lead inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${company.lead_status_class || 'bg-gray-100 text-gray-700'}`;
            leadCell.textContent = company.lead_status_label;
        } else {
            leadCell.className = 'js-company-lead text-gray-400';
            leadCell.textContent = '-';
        }
    }

    const editBtn = row.querySelector('.edit-company-btn');
    if (editBtn) {
        editBtn.setAttribute('data-company', JSON.stringify({
            name: company.name || '',
            company_title: company.company_title || '',
            tax_office: company.tax_office || '',
            tax_number: company.tax_number || '',
            phone: company.phone || '',
            email: company.email || '',
            address: company.address || '',
            city: company.city || '',
            district: company.district || '',
            website: company.website || '',
            google_category: company.google_category || '',
            activity_area: company.activity_area || '',
            lead_status: company.lead_status || ''
        }));
    }

    lucide.createIcons();
}

function openEditModal(companyId, data) {
    const form = document.getElementById('editForm');
    form.action = companyUpdateUrlTemplate.replace('__COMPANY_ID__', companyId);
    document.getElementById('edit-name').value = data.name || '';
    document.getElementById('edit-company_title').value = data.company_title || '';
    document.getElementById('edit-tax_office').value = data.tax_office || '';
    document.getElementById('edit-tax_number').value = data.tax_number || '';
    document.getElementById('edit-phone').value = data.phone || '';
    document.getElementById('edit-email').value = data.email || '';
    document.getElementById('edit-address').value = data.address || '';
    document.getElementById('edit-city').value = data.city || '';
    document.getElementById('edit-district').value = data.district || '';
    document.getElementById('edit-website').value = data.website || '';
    document.getElementById('edit-google_category').value = data.google_category || '';
    document.getElementById('edit-activity_area').value = data.activity_area || '';
    document.getElementById('edit-lead_status').value = data.lead_status || '';
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    setTimeout(() => lucide.createIcons(), 50);
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeEditModal(); });

document.addEventListener('DOMContentLoaded', function () {
    const toastCloseBtn = document.getElementById('companies-toast-close');
    if (toastCloseBtn) {
        toastCloseBtn.addEventListener('click', function () {
            clearTimeout(window.__companiesToastTimer);
            hideToast();
        });
    }

    // Edit buttons
    document.querySelectorAll('.edit-company-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = btn.dataset.id;
            let data = {};
            try {
                data = JSON.parse(btn.getAttribute('data-company') || '{}');
            } catch (error) {
                console.error('Firma verisi parse edilemedi:', error);
                return;
            }
            openEditModal(id, data);
        });
    });

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const submitBtn = editForm.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Kaydediliyor...';
            }

            try {
                const formData = new FormData(editForm);
                const response = await fetch(editForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();
                if (!response.ok) {
                    showToast(result.message || 'Kaydetme sırasında hata oluştu.', 'error');
                    return;
                }

                if (result.company) {
                    updateCompanyRow(result.company);
                }

                closeEditModal();
                showToast(result.message || 'Firma bilgileri güncellendi.');
            } catch (error) {
                showToast('Bağlantı hatası oluştu. Lütfen tekrar deneyin.', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }
        });
    }

    const checkAll = document.getElementById('check-all-companies');
    const checkboxes = document.querySelectorAll('.company-checkbox');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            checkboxes.forEach((cb) => { cb.checked = checkAll.checked; });
        });
    }
    lucide.createIcons();
});
</script>
@endsection