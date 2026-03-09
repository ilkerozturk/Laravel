<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Lead;
use App\Models\PlaceImportLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query()->latest();

        if ($request->filled('q')) {
            $searchValue = trim((string) $request->string('q'));
            $like = '%' . $searchValue . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'LIKE', $like)
                    ->orWhere('phone', 'LIKE', $like)
                    ->orWhere('email', 'LIKE', $like)
                    ->orWhere('activity_area', 'LIKE', $like);
            });
        }
        if ($request->filled('activity_area')) {
            $query->where('activity_area', $request->string('activity_area'));
        }
        if ($request->filled('city')) {
            $query->where('city', $request->string('city'));
        }
        if ($request->filled('district')) {
            $query->where('district', $request->string('district'));
        }
        if ($request->boolean('no_website')) {
            $query->where(function ($q) {
                $q->whereNull('website')->orWhere('website', '');
            });
        }

        $companies = $query->with(['lead'])->paginate(20)->withQueryString();
        $cities = Company::query()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
        $districts = Company::query()
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->distinct()
            ->orderBy('district')
            ->pluck('district');
        $activityAreasFromCompanies = Company::query()
            ->whereNotNull('activity_area')
            ->where('activity_area', '!=', '')
            ->distinct()
            ->orderBy('activity_area')
            ->pluck('activity_area');
        $activityAreas = collect(array_values($this->activityAreaMap()))
            ->merge($activityAreasFromCompanies)
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->unique()
            ->sort()
            ->values();

        return view('companies.index', compact('companies', 'cities', 'districts', 'activityAreas'));
    }

    public function importLogs()
    {
        $importLogs = PlaceImportLog::query()->latest()->paginate(20);

        return view('import-logs.index', compact('importLogs'));
    }

    public function destroyImportLog(PlaceImportLog $importLog): RedirectResponse
    {
        $importLog->delete();

        return redirect()->route('companies.import-logs')->with('status', 'Import kaydı silindi.');
    }

    public function bulkDestroyImportLogs(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'import_log_ids' => ['required', 'array', 'min:1'],
            'import_log_ids.*' => ['integer'],
        ]);

        $deletedCount = PlaceImportLog::query()
            ->whereIn('id', $data['import_log_ids'])
            ->delete();

        return redirect()->route('companies.import-logs')->with('status', "Toplu silme tamamlandi. Silinen kayıt: {$deletedCount}");
    }

    public function clearImportLogs(): RedirectResponse
    {
        $deletedCount = PlaceImportLog::query()->delete();

        return redirect()->route('companies.import-logs')->with('status', "Tum import loglari silindi. Silinen kayıt: {$deletedCount}");
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'place_id' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:190'],
            'company_title' => ['nullable', 'string', 'max:255'],
            'tax_office' => ['nullable', 'string', 'max:190'],
            'tax_number' => ['nullable', 'string', 'max:64'],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'string', 'max:255'],
            'google_category' => ['nullable', 'string', 'max:190'],
            'activity_area' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($data['place_id'])) {
            $data['place_id'] = 'manual_' . uniqid();
        }

        $company = Company::create($data);

        if (empty($company->website)) {
            Lead::firstOrCreate([
                'company_id' => $company->id,
            ], [
                'status' => Lead::STATUS_POSTPONED,
            ]);
        }

        return redirect()->route('companies.index')->with('status', 'Firma kaydedildi.');
    }

    public function update(Request $request, Company $company): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'company_title' => ['nullable', 'string', 'max:255'],
            'tax_office' => ['nullable', 'string', 'max:190'],
            'tax_number' => ['nullable', 'string', 'max:64'],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'string', 'max:255'],
            'google_category' => ['nullable', 'string', 'max:190'],
            'activity_area' => ['nullable', 'string', 'max:255'],
            'lead_status' => ['nullable', Rule::in(array_keys(Lead::statusOptions()))],
        ]);

        $companyData = $data;
        unset($companyData['lead_status']);

        $company->update($companyData);

        if (!empty($data['lead_status'])) {
            Lead::query()->updateOrCreate(
                ['company_id' => $company->id],
                ['status' => $data['lead_status']]
            );
        }

        $lead = Lead::query()->where('company_id', $company->id)->first();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Firma bilgileri güncellendi.',
                'company' => [
                    'id' => $company->id,
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
                    'lead_status' => $lead?->status,
                    'lead_status_label' => $lead ? Lead::statusLabel($lead->status) : null,
                    'lead_status_class' => $lead ? Lead::statusBadgeClass($lead->status) : null,
                ],
            ]);
        }

        return redirect()->route('companies.index')->with('status', 'Firma bilgileri güncellendi.');
    }

    public function importPlaces(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'city' => ['required', 'string', 'max:120'],
            'district' => ['required', 'string', 'max:120'],
            'keyword' => ['nullable', 'string', 'max:190'],
            'max_pages' => ['nullable', 'integer', 'min:1', 'max:3'],
        ]);

        $apiKey = (string) config('services.google.places_api_key', env('GOOGLE_PLACES_API_KEY'));
        if ($apiKey === '') {
            return back()->with('status', 'Google Places API key eksik. .env dosyasina GOOGLE_PLACES_API_KEY ekleyin.');
        }

        $maxPages = (int) ($data['max_pages'] ?? 1);

        $searchQueries = $this->buildPlaceSearchQueries($data);
        $stats = [
            'created_count' => 0,
            'updated_count' => 0,
            'skipped_count' => 0,
            'new_lead_count' => 0,
            'page_count' => 0,
            'fetched_result_count' => 0,
            'api_status' => null,
            'api_error_message' => null,
        ];

        foreach ($searchQueries as $searchQuery) {
            $currentStats = $this->importPlacesForQuery($searchQuery, $apiKey, $data, $maxPages);
            if (($currentStats['fetched_result_count'] ?? 0) > 0) {
                $stats = $currentStats;
                break;
            }

            if (!empty($currentStats['api_status'])) {
                $stats['api_status'] = $currentStats['api_status'];
                $stats['api_error_message'] = $currentStats['api_error_message'] ?? null;
            }
        }

        $createdCount = (int) ($stats['created_count'] ?? 0);
        $updatedCount = (int) ($stats['updated_count'] ?? 0);
        $skippedCount = (int) ($stats['skipped_count'] ?? 0);
        $newLeadCount = (int) ($stats['new_lead_count'] ?? 0);
        $pageCount = (int) ($stats['page_count'] ?? 0);
        $fetchedResultCount = (int) ($stats['fetched_result_count'] ?? 0);

        if ($fetchedResultCount === 0) {
            if (!empty($stats['api_status']) && $stats['api_status'] !== 'ZERO_RESULTS') {
                $apiMessage = 'Google Places hatasi: ' . $stats['api_status'];
                if (!empty($stats['api_error_message'])) {
                    $apiMessage .= ' (' . $stats['api_error_message'] . ')';
                }

                return back()->with('status', $apiMessage);
            }

            return back()->with('status', 'Google Places sonucunda kayit bulunamadi.');
        }

        PlaceImportLog::create([
            'city' => $data['city'],
            'district' => $data['district'],
            'keyword' => $data['keyword'] ?? null,
            'max_pages' => $maxPages,
            'pages_processed' => $pageCount,
            'fetched_result_count' => $fetchedResultCount,
            'created_count' => $createdCount,
            'updated_count' => $updatedCount,
            'skipped_count' => $skippedCount,
            'new_lead_count' => $newLeadCount,
            'executed_at' => now(),
        ]);

        return back()->with(
            'status',
            "Google Places import tamamlandi. Sayfa: {$pageCount}, cekilen: {$fetchedResultCount}, yeni: {$createdCount}, guncellendi: {$updatedCount}, atlandi: {$skippedCount}, yeni lead: {$newLeadCount}."
        );
    }

    private function buildPlaceSearchQueries(array $data): array
    {
        $keyword = trim((string) ($data['keyword'] ?? ''));
        $district = trim((string) ($data['district'] ?? ''));
        $city = trim((string) ($data['city'] ?? ''));

        $query1 = trim(implode(' ', array_filter([$keyword, $district, $city])));
        $query2 = trim(implode(' ', array_filter([$keyword, $city, $district])));
        $query3 = trim(implode(' ', array_filter([$district, $city, $keyword])));

        return array_values(array_filter(array_unique([
            $query1,
            $query2,
            $query3,
            trim(Str::ascii($query1)),
            trim(Str::ascii($query2)),
            trim(Str::ascii($query3)),
        ])));
    }

    private function importPlacesForQuery(string $query, string $apiKey, array $data, int $maxPages): array
    {
        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $newLeadCount = 0;
        $pageCount = 0;
        $fetchedResultCount = 0;
        $nextPageToken = null;

        do {
            $params = [
                'language' => 'tr',
                'region' => 'tr',
                'key' => $apiKey,
            ];

            if ($nextPageToken) {
                sleep(2);
                $params['pagetoken'] = $nextPageToken;
            } else {
                $params['query'] = $query;
            }

            $searchResponse = Http::timeout(20)
                ->get('https://maps.googleapis.com/maps/api/place/textsearch/json', $params)
                ->json();

            $apiStatus = (string) ($searchResponse['status'] ?? '');
            if ($apiStatus !== '' && !in_array($apiStatus, ['OK', 'ZERO_RESULTS'], true)) {
                return [
                    'created_count' => 0,
                    'updated_count' => 0,
                    'skipped_count' => 0,
                    'new_lead_count' => 0,
                    'page_count' => $pageCount,
                    'fetched_result_count' => 0,
                    'api_status' => $apiStatus,
                    'api_error_message' => (string) ($searchResponse['error_message'] ?? ''),
                ];
            }

            $results = $searchResponse['results'] ?? [];
            if (!is_array($results)) {
                $results = [];
            }
            $fetchedResultCount += count($results);

            foreach ($results as $result) {
                $placeId = (string) ($result['place_id'] ?? '');
                if ($placeId === '') {
                    continue;
                }

                $detailsResponse = Http::timeout(20)->get('https://maps.googleapis.com/maps/api/place/details/json', [
                    'place_id' => $placeId,
                    'fields' => 'place_id,name,formatted_address,formatted_phone_number,website,types',
                    'language' => 'tr',
                    'region' => 'tr',
                    'key' => $apiKey,
                ])->json();

                $details = $detailsResponse['result'] ?? [];
                $types = $details['types'] ?? ($result['types'] ?? []);
                $name = (string) ($details['name'] ?? ($result['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $company = Company::firstOrNew(['place_id' => $placeId]);
                $isNew = !$company->exists;
                $company->fill([
                    'name' => $name,
                    'phone' => $details['formatted_phone_number'] ?? null,
                    'address' => $details['formatted_address'] ?? null,
                    'city' => $data['city'],
                    'district' => $data['district'],
                    'website' => $details['website'] ?? null,
                    'google_category' => isset($types[0]) ? (string) $types[0] : null,
                    'activity_area' => $this->detectActivityArea(is_array($types) ? $types : [], $name),
                ]);

                if ($isNew || $company->isDirty()) {
                    $company->save();
                    if ($isNew) {
                        $createdCount++;
                    } else {
                        $updatedCount++;
                    }
                } else {
                    $skippedCount++;
                }

                if (empty($company->website)) {
                    $lead = Lead::firstOrCreate(
                        ['company_id' => $company->id],
                        ['status' => Lead::STATUS_POSTPONED]
                    );
                    if ($lead->wasRecentlyCreated) {
                        $newLeadCount++;
                    }
                }
            }

            $pageCount++;
            $nextPageToken = $searchResponse['next_page_token'] ?? null;
        } while ($nextPageToken && $pageCount < $maxPages);

        return [
            'created_count' => $createdCount,
            'updated_count' => $updatedCount,
            'skipped_count' => $skippedCount,
            'new_lead_count' => $newLeadCount,
            'page_count' => $pageCount,
            'fetched_result_count' => $fetchedResultCount,
            'api_status' => null,
            'api_error_message' => null,
        ];
    }

    public function destroy(Company $company): RedirectResponse
    {
        $deleted = $this->deleteCompanyWithRelations($company);
        if (!$deleted) {
            return redirect()->route('companies.index')->with('status', 'Firma silinemedi.');
        }

        return redirect()->route('companies.index')->with('status', 'Firma silindi.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_ids' => ['required', 'array', 'min:1'],
            'company_ids.*' => ['integer'],
        ]);

        $companies = Company::query()->whereIn('id', $data['company_ids'])->get();
        $deletedCount = 0;
        foreach ($companies as $company) {
            if ($this->deleteCompanyWithRelations($company)) {
                $deletedCount++;
            }
        }

        return redirect()->route('companies.index')->with('status', "Toplu silme tamamlandi. Silinen firma: {$deletedCount}");
    }

    private function deleteCompanyWithRelations(Company $company): bool
    {
        try {
            DB::transaction(function () use ($company): void {
                $lead = Lead::query()->where('company_id', $company->id)->first();
                if ($lead) {
                    DB::table('follow_ups')->where('lead_id', $lead->id)->delete();
                    DB::table('outreach_emails')->where('lead_id', $lead->id)->delete();
                    $lead->delete();
                }
                $company->delete();
            });

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function detectActivityArea(array $types, string $name): ?string
    {
        $haystack = mb_strtolower(implode(' ', $types) . ' ' . $name);

        foreach ($this->activityAreaMap() as $keyword => $label) {
            if (str_contains($haystack, $keyword)) {
                return $label;
            }
        }

        return null;
    }

    private function activityAreaMap(): array
    {
        return [
            'restaurant' => 'Restoran',
            'cafe' => 'Kafe',
            'bakery' => 'Firincilik',
            'meal_takeaway' => 'Paket Servis',
            'meal_delivery' => 'Yemek Teslimat',
            'beauty' => 'Guzellik Hizmetleri',
            'hair' => 'Kuafor Hizmetleri',
            'dentist' => 'Dis Klinigi',
            'doctor' => 'Saglik Hizmetleri',
            'hospital' => 'Hastane',
            'pharmacy' => 'Eczane',
            'lawyer' => 'Hukuk Hizmetleri',
            'real_estate' => 'Gayrimenkul',
            'car_repair' => 'Oto Servis',
            'car_dealer' => 'Oto Bayi',
            'car_rental' => 'Arac Kiralama',
            'gas_station' => 'Akaryakit Istasyonu',
            'plumber' => 'Tesisat Hizmetleri',
            'electrician' => 'Elektrik Hizmetleri',
            'store' => 'Perakende',
            'shopping_mall' => 'Alisveris Merkezi',
            'supermarket' => 'Supermarket',
            'hardware_store' => 'Hirdavat',
            'home_goods_store' => 'Ev Gerecleri',
            'furniture_store' => 'Mobilya',
            'pet_store' => 'Pet Shop',
            'accounting' => 'Muhasebe Hizmetleri',
            'bank' => 'Bankacilik',
            'insurance_agency' => 'Sigortacilik',
            'school' => 'Egitim Hizmetleri',
            'gym' => 'Spor ve Fitness',
            'lodging' => 'Konaklama',
            'hotel' => 'Otel',
            'tourist_attraction' => 'Turizm',
            'travel_agency' => 'Seyahat Acentasi',
            'manufactur' => 'Imalat Sanayi',
        ];
    }
}
