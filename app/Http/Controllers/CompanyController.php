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

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query()->latest();

        if ($request->filled('q')) {
            $qValue = mb_strtolower((string) $request->string('q'));
            $like = '%' . $qValue . '%';
            $query->where(function ($q) use ($like) {
                $q->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(phone) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(activity_area) LIKE ?', [$like]);
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

        $companies = $query->with(['lead', 'latestDemoProject'])->paginate(20)->withQueryString();
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
        $activityAreas = Company::query()
            ->whereNotNull('activity_area')
            ->where('activity_area', '!=', '')
            ->distinct()
            ->orderBy('activity_area')
            ->pluck('activity_area');

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
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'string', 'max:255'],
            'google_category' => ['nullable', 'string', 'max:190'],
            'activity_area' => ['nullable', 'string', 'max:255'],
            'demo_prompt' => ['nullable', 'string'],
        ]);

        if (empty($data['place_id'])) {
            $data['place_id'] = 'manual_' . uniqid();
        }

        $company = Company::create($data);

        if (empty($company->website)) {
            Lead::firstOrCreate([
                'company_id' => $company->id,
            ], [
                'status' => 'new',
            ]);
        }

        return redirect()->route('companies.index')->with('status', 'Firma kaydedildi.');
    }

    public function update(Request $request, Company $company): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'string', 'max:255'],
            'google_category' => ['nullable', 'string', 'max:190'],
            'activity_area' => ['nullable', 'string', 'max:255'],
        ]);

        $company->update($data);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Firma bilgileri güncellendi.',
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'phone' => $company->phone,
                    'email' => $company->email,
                    'address' => $company->address,
                    'city' => $company->city,
                    'district' => $company->district,
                    'website' => $company->website,
                    'google_category' => $company->google_category,
                    'activity_area' => $company->activity_area,
                ],
            ]);
        }

        return redirect()->route('companies.index')->with('status', 'Firma bilgileri güncellendi.');
    }

    public function updateDemoPrompt(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'demo_prompt' => ['required', 'string', 'max:20000'],
        ]);

        $company->update([
            'demo_prompt' => $data['demo_prompt'],
        ]);

        return redirect()->route('companies.index')->with('status', 'Demo prompt kaydedildi.');
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

        $query = trim(($data['keyword'] ?? '') . ' ' . $data['district'] . ' ' . $data['city']);
        $maxPages = (int) ($data['max_pages'] ?? 1);

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
                // Google may return INVALID_REQUEST for a short period before token becomes active.
                sleep(2);
                $params['pagetoken'] = $nextPageToken;
            } else {
                $params['query'] = $query;
            }

            $searchResponse = Http::timeout(20)
                ->get('https://maps.googleapis.com/maps/api/place/textsearch/json', $params)
                ->json();

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
                        ['status' => 'new']
                    );
                    if ($lead->wasRecentlyCreated) {
                        $newLeadCount++;
                    }
                }
            }

            $pageCount++;
            $nextPageToken = $searchResponse['next_page_token'] ?? null;
        } while ($nextPageToken && $pageCount < $maxPages);

        if ($fetchedResultCount === 0) {
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
                    DB::table('demo_sites')->where('lead_id', $lead->id)->delete();
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
        $map = [
            'restaurant' => 'Restoran',
            'cafe' => 'Kafe',
            'bakery' => 'Firincilik',
            'beauty' => 'Guzellik Hizmetleri',
            'hair' => 'Kuafor Hizmetleri',
            'dentist' => 'Dis Klinigi',
            'doctor' => 'Saglik Hizmetleri',
            'lawyer' => 'Hukuk Hizmetleri',
            'real_estate' => 'Gayrimenkul',
            'car_repair' => 'Oto Servis',
            'plumber' => 'Tesisat Hizmetleri',
            'electrician' => 'Elektrik Hizmetleri',
            'store' => 'Perakende',
            'accounting' => 'Muhasebe Hizmetleri',
            'school' => 'Egitim Hizmetleri',
            'gym' => 'Spor ve Fitness',
            'manufactur' => 'Imalat Sanayi',
        ];

        foreach ($map as $keyword => $label) {
            if (str_contains($haystack, $keyword)) {
                return $label;
            }
        }

        return null;
    }
}
