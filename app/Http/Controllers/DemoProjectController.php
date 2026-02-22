<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Company;
use App\Models\DemoProject;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use ZipArchive;

class DemoProjectController extends Controller
{
    public function generate(Company $company): RedirectResponse
    {
        try {
            $demo = $this->createDemoProject($company);
            $this->executeGeneration($demo);
            return back()->with('status', 'Demo hazirlandi. Cloud Opus tarafindan olusturulan benzersiz dosyalar ziplenerek kaydedildi.');
        } catch (\Throwable $e) {
            return back()->with('status', 'Demo olusturma basarisiz: ' . $e->getMessage());
        }
    }

    public function start(Company $company): JsonResponse
    {
        if (!trim((string) $company->demo_prompt)) {
            return response()->json(['message' => 'Bu firma icin once demo prompt girip kaydetmelisiniz.'], 422);
        }

        $demo = $this->createDemoProject($company);
        return response()->json([
            'demo_project_id' => $demo->id,
            'progress_percent' => (int) $demo->progress_percent,
            'status' => $demo->status,
        ]);
    }

    public function run(DemoProject $demoProject): JsonResponse
    {
        if ($demoProject->status === 'generated') {
            return response()->json([
                'message' => 'Demo zaten uretilmis.',
                'progress_percent' => 100,
                'download_url' => route('demo-projects.download', $demoProject),
            ]);
        }

        try {
            $this->executeGeneration($demoProject);
            $demoProject->refresh();
            return response()->json([
                'message' => 'Demo hazirlandi.',
                'progress_percent' => 100,
                'download_url' => route('demo-projects.download', $demoProject),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function progress(DemoProject $demoProject): JsonResponse
    {
        return response()->json([
            'id' => $demoProject->id,
            'status' => $demoProject->status,
            'progress_percent' => (int) ($demoProject->progress_percent ?? 0),
            'can_download' => $demoProject->status === 'generated',
            'download_url' => $demoProject->status === 'generated' ? route('demo-projects.download', $demoProject) : null,
            'error_message' => $demoProject->status === 'failed' ? $demoProject->error_message : null,
        ]);
    }

    public function download(DemoProject $demoProject)
    {
        if ($demoProject->status !== 'generated' || !$demoProject->zip_path || !File::exists($demoProject->zip_path)) {
            abort(404);
        }

        $filename = Str::slug($demoProject->company->name) . '-premium-demo.zip';

        return response()->download($demoProject->zip_path, $filename);
    }

    private function createDemoProject(Company $company): DemoProject
    {
        $prompt = $this->buildPrompt($company);
        return DemoProject::create([
            'company_id' => $company->id,
            'title' => $company->name . ' Premium Demo',
            'status' => 'pending',
            'prompt_text' => $prompt,
            'download_token' => Str::random(40),
            'progress_percent' => 0,
            'error_message' => null,
        ]);
    }

    private function executeGeneration(DemoProject $demo): void
    {
        $company = $demo->company()->firstOrFail();
        $prompt = $demo->prompt_text ?: $this->buildPrompt($company);

        $this->updateProgress($demo, 5);

        try {
            $token = date('YmdHis') . '-' . Str::slug($company->name) . '-' . Str::random(6);
            $baseDir = storage_path('app/demo-projects/' . $token);
            $zipDir = storage_path('app/demo-zips');
            if (!File::isDirectory($baseDir)) {
                File::makeDirectory($baseDir, 0755, true);
            }
            if (!File::isDirectory($zipDir)) {
                File::makeDirectory($zipDir, 0755, true);
            }

            $this->updateProgress($demo, 20);
            $content = $this->buildContentPayload($company, $prompt);

            $this->updateProgress($demo, 45);
            $this->writeTemplateFiles($baseDir, $content, $company, $prompt);

            $this->updateProgress($demo, 80);
            $zipPath = $zipDir . '/' . $token . '.zip';
            $this->zipDirectory($baseDir, $zipPath);

            $demo->update([
                'status' => 'generated',
                'folder_path' => $baseDir,
                'zip_path' => $zipPath,
                'progress_percent' => 100,
                'error_message' => null,
            ]);

            $lead = Lead::firstOrCreate(
                ['company_id' => $company->id],
                ['status' => 'demo_ready']
            );
            if (!$lead->wasRecentlyCreated && !in_array($lead->status, ['won', 'lost'], true)) {
                $lead->update(['status' => 'demo_ready']);
            }
        } catch (\Throwable $e) {
            $demo->update([
                'status' => 'failed',
                'progress_percent' => 100,
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function updateProgress(DemoProject $demo, int $percent): void
    {
        $demo->update(['progress_percent' => max(0, min(100, $percent))]);
    }

    private function buildPrompt(Company $company): string
    {
        $manualPrompt = trim((string) $company->demo_prompt);

        return <<<TXT
Generate premium Turkish corporate website content in JSON for this business.
Company: {$company->name}
Sector: {$company->activity_area}
Category: {$company->google_category}
City: {$company->city}
District: {$company->district}
Phone: {$company->phone}
Email: {$company->email}
Address: {$company->address}

Manual business brief from operator:
{$manualPrompt}

Return ONLY valid JSON (no markdown) with keys:
site{name,logo_text,tagline,phone,email,address,primary_color,secondary_color,analytics_snippet,google_translate_enabled,google_maps_embed_url,recaptcha_site_key,logo_url,hero_image}
hero{title,subtitle,cta_primary,cta_secondary}
about{content,mission,vision}
services[{title,description} x at least 4]
references[{name,detail,image} x at least 3]
faq[{question,answer} x at least 4]
TXT;
    }

    private function buildContentPayload(Company $company, string $prompt): array
    {
        $fallback = $this->buildFallbackContent($company);

        $apiKey = trim((string) AppSetting::getValue('cloud_opus_api_key', (string) env('CLOUD_OPUS_API_KEY', '')));
        if (!$apiKey) {
            return $fallback;
        }

        try {
            $systemPrompt = 'You are a web copywriter and return strict JSON only. Never add markdown fences.';
            $decoded = $this->requestCloudOpusJson($systemPrompt, $prompt, 90);
            if (!is_array($decoded)) {
                return $fallback;
            }

            return $this->mergeContent($fallback, $decoded);
        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    private function requestCloudOpusJson(string $systemPrompt, string $userPrompt, int $timeoutSeconds = 120): ?array
    {
        $apiKey = trim((string) AppSetting::getValue('cloud_opus_api_key', (string) env('CLOUD_OPUS_API_KEY', '')));
        $model = trim((string) AppSetting::getValue('cloud_opus_model', (string) env('CLOUD_OPUS_MODEL', 'claude-opus-4-1-20250805')));
        $baseUrl = trim((string) AppSetting::getValue('cloud_opus_base_url', (string) env('CLOUD_OPUS_BASE_URL', 'https://api.anthropic.com/v1/messages')));
        $maxTokens = (int) AppSetting::getValue('cloud_opus_max_tokens', (string) env('CLOUD_OPUS_MAX_TOKENS', '2400'));

        if ($apiKey === '' || $model === '' || $baseUrl === '') {
            return null;
        }

        $response = Http::timeout($timeoutSeconds)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post($baseUrl, [
                'model' => $model,
                'max_tokens' => max(256, min(8192, $maxTokens)),
                'system' => $systemPrompt,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $userPrompt],
                        ],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            return null;
        }

        $text = (string) data_get($response->json(), 'content.0.text', '');
        if ($text === '') {
            return null;
        }

        return $this->decodeJsonObject($text);
    }

    private function buildFallbackContent(Company $company): array
    {
        $logoText = mb_strtoupper(mb_substr($company->name, 0, 1));

        return [
            'site' => [
                'name' => $company->name,
                'logo_text' => $logoText,
                'tagline' => ($company->activity_area ?: 'Profesyonel Hizmet') . ' alaninda premium cozumler',
                'phone' => $company->phone ?: '',
                'email' => $company->email ?: '',
                'address' => $company->address ?: trim(($company->district ?: '') . ' ' . ($company->city ?: '')),
                'primary_color' => '#0f172a',
                'secondary_color' => '#0ea5e9',
                'analytics_snippet' => '',
                'google_translate_enabled' => true,
                'google_maps_embed_url' => '',
                'recaptcha_site_key' => '',
                'logo_url' => '',
                'hero_image' => '',
            ],
            'hero' => [
                'title' => $company->name . ' ile premium dijital vitrin',
                'subtitle' => 'Musterilerinizde guven olusturan modern, hizli ve yonetilebilir web deneyimi.',
                'cta_primary' => 'Hemen Teklif Al',
                'cta_secondary' => 'Projelerimizi Incele',
            ],
            'about' => [
                'content' => $company->name . ' olarak kalite, hiz ve guven odakli hizmet sunuyoruz.',
                'mission' => 'Musterilerimize olculenebilir deger katmak.',
                'vision' => 'Sektorunde dijital lider cozum ortagi olmak.',
            ],
            'services' => [
                ['title' => 'Kurumsal Web Tasarim', 'description' => 'Marka kimliginize uygun premium arayuz.'],
                ['title' => 'Icerik ve SEO Altyapisi', 'description' => 'Arama motoru dostu sayfa yapisi ve metinler.'],
                ['title' => 'Form ve Lead Toplama', 'description' => 'Donusum odakli iletisim altyapisi.'],
                ['title' => 'Bakim ve Gelistirme', 'description' => 'Yonetim paneliyle hizli guncelleme.'],
            ],
            'references' => [
                ['name' => 'Kurumsal Referans A', 'detail' => 'Premium kurumsal web donusum projesi.', 'image' => ''],
                ['name' => 'Kurumsal Referans B', 'detail' => 'Yuksek donusumlu tanitim sayfasi.', 'image' => ''],
                ['name' => 'Kurumsal Referans C', 'detail' => 'Sektor odakli icerik stratejisi.', 'image' => ''],
            ],
            'faq' => [
                ['question' => 'Proje ne kadar surer?', 'answer' => 'Demo calisma kisa surede teslim edilir.'],
                ['question' => 'Icerik degisebilir mi?', 'answer' => 'Evet, yonetim panelinden tum metinler duzenlenebilir.'],
                ['question' => 'Mobil uyumlu mu?', 'answer' => 'Evet, demo responsive yapida hazirlanir.'],
                ['question' => 'Destek veriliyor mu?', 'answer' => 'Kurulum ve guncelleme destegi sunulur.'],
            ],
        ];
    }

    private function mergeContent(array $fallback, array $ai): array
    {
        $merged = $fallback;
        foreach (['site', 'hero', 'about'] as $section) {
            if (isset($ai[$section]) && is_array($ai[$section])) {
                $merged[$section] = array_merge($merged[$section], $ai[$section]);
            }
        }
        foreach (['services', 'references', 'faq'] as $section) {
            if (isset($ai[$section]) && is_array($ai[$section]) && count($ai[$section]) > 0) {
                $merged[$section] = array_values($ai[$section]);
            }
        }

        return $merged;
    }

    private function writeTemplateFiles(string $baseDir, array $content, Company $company, string $prompt): void
    {
        File::makeDirectory($baseDir . '/data', 0755, true, true);
        File::put($baseDir . '/data/content.json', json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $files = $this->buildWebsiteFilesFromCloudOpus($company, $content, $prompt);

        foreach ($files as $file) {
            $relativePath = $this->normalizeWebsiteFilePath((string) ($file['path'] ?? ''));
            if ($relativePath === null) {
                continue;
            }

            $absolutePath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            File::ensureDirectoryExists(dirname($absolutePath));
            File::put($absolutePath, (string) ($file['content'] ?? ''));
        }

        if (!File::exists($baseDir . '/index.html')) {
            $fallbackFiles = $this->buildFallbackWebsiteFiles($content);
            foreach ($fallbackFiles as $file) {
                $absolutePath = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file['path']);
                File::ensureDirectoryExists(dirname($absolutePath));
                File::put($absolutePath, $file['content']);
            }
        }

        if (!File::exists($baseDir . '/.htaccess')) {
            $this->writeHtaccess($baseDir);
        }
    }

    private function buildWebsiteFilesFromCloudOpus(Company $company, array $content, string $prompt): array
    {
        $operatorPrompt = trim((string) $company->demo_prompt);
        $needsAdminFile = str_contains(mb_strtolower($operatorPrompt), 'admin.html');

        $systemPrompt = 'You are an expert frontend engineer. OPERATOR PROMPT IS MANDATORY and has higher priority than all defaults. Return ONLY valid JSON object with this exact shape: {"files":[{"path":"index.html","content":"..."}]}. No markdown, no comments, no explanations. Generate complete deployable static files. All text must be Turkish unless operator asks otherwise.';
        $userPrompt = "OPERATOR_PROMPT_START\n{$operatorPrompt}\nOPERATOR_PROMPT_END\n\nUse company context and structured content as source material, but if there is any conflict, follow OPERATOR_PROMPT exactly.\nCompany: {$company->name}\nSector: {$company->activity_area}\nCity: {$company->city}\nDistrict: {$company->district}\nPhone: {$company->phone}\nEmail: {$company->email}\nAddress: {$company->address}\n\nOUTPUT RULES:\n1) Return JSON only.\n2) JSON root key must be files.\n3) Each file item needs path and content.\n4) If operator requested specific files (e.g. index.html/admin.html), include them exactly.\n5) Do not omit required sections from operator prompt.\n\nSTRUCTURED_CONTENT_JSON:\n" . json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $decoded = $this->requestCloudOpusJson($systemPrompt, $userPrompt, 180);
        $files = data_get($decoded, 'files');
        if (!is_array($files) || count($files) === 0) {
            return $this->buildFallbackWebsiteFiles($content);
        }

        $normalized = [];
        foreach ($files as $file) {
            if (!is_array($file)) {
                continue;
            }
            $path = (string) ($file['path'] ?? '');
            $contentText = (string) ($file['content'] ?? '');
            if ($path === '' || $contentText === '') {
                continue;
            }
            $normalized[] = ['path' => $path, 'content' => $contentText];
        }

        if (count($normalized) === 0) {
            return $this->buildFallbackWebsiteFiles($content);
        }

        $hasIndex = false;
        foreach ($normalized as $file) {
            if (strtolower((string) $file['path']) === 'index.html') {
                $hasIndex = true;
                break;
            }
        }

        if (!$hasIndex) {
            $normalized = array_merge($this->buildFallbackWebsiteFiles($content), $normalized);
        }

                if ($needsAdminFile) {
                        $hasAdmin = false;
                        foreach ($normalized as $file) {
                                if (strtolower((string) $file['path']) === 'admin.html') {
                                        $hasAdmin = true;
                                        break;
                                }
                        }

                        if (!$hasAdmin) {
                                $normalized[] = [
                                        'path' => 'admin.html',
                                        'content' => $this->buildFallbackAdminHtml($content),
                                ];
                        }
                }

        return $normalized;
    }

        private function buildFallbackAdminHtml(array $content): string
        {
                $siteName = htmlspecialchars((string) data_get($content, 'site.name', 'Site Admin'), ENT_QUOTES, 'UTF-8');
                $defaultConfig = htmlspecialchars(json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');

                return <<<HTML
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{$siteName} - Admin</title>
    <style>body{font-family:Arial,sans-serif;background:#0f172a;color:#fff;margin:0;padding:20px}textarea{width:100%;min-height:320px;border:1px solid #334155;background:#111827;color:#e2e8f0;padding:12px}button{margin-top:12px;padding:10px 16px;border:0;background:#2563eb;color:#fff;cursor:pointer;font-weight:700}</style>
</head>
<body>
    <h1>{$siteName} Yönetim</h1>
    <p>siteConfig JSON alanını güncelleyip kaydedin.</p>
    <textarea id="cfg">{$defaultConfig}</textarea>
    <button id="saveBtn">Kaydet (localStorage)</button>
    <script>
        const key = 'siteConfig';
        const area = document.getElementById('cfg');
        const saved = localStorage.getItem(key);
        if (saved) area.value = saved;
        document.getElementById('saveBtn').addEventListener('click', () => {
            try {
                JSON.parse(area.value);
                localStorage.setItem(key, area.value);
                alert('Kaydedildi');
            } catch (e) {
                alert('Geçersiz JSON');
            }
        });
    </script>
</body>
</html>
HTML;
        }

    private function buildFallbackWebsiteFiles(array $content): array
    {
        $siteName = htmlspecialchars((string) data_get($content, 'site.name', 'Kurumsal Firma'), ENT_QUOTES, 'UTF-8');
        $tagline = htmlspecialchars((string) data_get($content, 'site.tagline', 'Kurumsal web sitesi'), ENT_QUOTES, 'UTF-8');
        $heroTitle = htmlspecialchars((string) data_get($content, 'hero.title', $siteName), ENT_QUOTES, 'UTF-8');
        $heroSubtitle = htmlspecialchars((string) data_get($content, 'hero.subtitle', ''), ENT_QUOTES, 'UTF-8');
        $phone = htmlspecialchars((string) data_get($content, 'site.phone', ''), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars((string) data_get($content, 'site.email', ''), ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars((string) data_get($content, 'site.address', ''), ENT_QUOTES, 'UTF-8');
        $primary = htmlspecialchars((string) data_get($content, 'site.primary_color', '#0f172a'), ENT_QUOTES, 'UTF-8');

        $servicesHtml = '';
        foreach ((array) data_get($content, 'services', []) as $service) {
            $title = htmlspecialchars((string) data_get($service, 'title', ''), ENT_QUOTES, 'UTF-8');
            $desc = htmlspecialchars((string) data_get($service, 'description', ''), ENT_QUOTES, 'UTF-8');
            if ($title === '' && $desc === '') {
                continue;
            }
            $servicesHtml .= "<article class=\"card\"><h3>{$title}</h3><p>{$desc}</p></article>";
        }

        $faqHtml = '';
        foreach ((array) data_get($content, 'faq', []) as $faq) {
            $question = htmlspecialchars((string) data_get($faq, 'question', ''), ENT_QUOTES, 'UTF-8');
            $answer = htmlspecialchars((string) data_get($faq, 'answer', ''), ENT_QUOTES, 'UTF-8');
            if ($question === '' && $answer === '') {
                continue;
            }
            $faqHtml .= "<details class=\"card\"><summary>{$question}</summary><p>{$answer}</p></details>";
        }

        $indexHtml = <<<HTML
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$siteName}</title>
  <meta name="description" content="{$tagline}">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <header class="container">
    <h1>{$siteName}</h1>
    <p>{$tagline}</p>
  </header>
  <main class="container">
    <section class="hero card">
      <h2>{$heroTitle}</h2>
      <p>{$heroSubtitle}</p>
    </section>
    <section>
      <h2>Hizmetler</h2>
      <div class="grid">{$servicesHtml}</div>
    </section>
    <section>
      <h2>SSS</h2>
      <div class="grid">{$faqHtml}</div>
    </section>
    <section class="card">
      <h2>İletişim</h2>
      <p>Telefon: {$phone}</p>
      <p>E-posta: {$email}</p>
      <p>Adres: {$address}</p>
    </section>
  </main>
</body>
</html>
HTML;

        $styleCss = <<<CSS
:root{--primary:{$primary};}
*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a}
.container{max-width:980px;margin:0 auto;padding:20px}
.card{background:#fff;border:1px solid #e2e8f0;padding:18px;margin:12px 0}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
h1,h2,h3{margin:0 0 8px}header{border-bottom:4px solid var(--primary);margin-bottom:12px}
details summary{cursor:pointer;font-weight:700}
CSS;

        return [
            ['path' => 'index.html', 'content' => $indexHtml],
            ['path' => 'assets/css/style.css', 'content' => $styleCss],
        ];
    }

    private function normalizeWebsiteFilePath(string $path): ?string
    {
        $path = trim(str_replace('\\', '/', $path));
        if ($path === '' || str_starts_with($path, '/') || str_contains($path, '../')) {
            return null;
        }

        $path = ltrim($path, './');
        if ($path === '') {
            return null;
        }

        return $path;
    }

    private function applyCompanyBrandingToTheme(string $baseDir, array $content): void
    {
        $siteName = (string) data_get($content, 'site.name', 'Demo Site');
        $tagline = (string) data_get($content, 'site.tagline', '');
        $heroSubtitle = (string) data_get($content, 'hero.subtitle', '');

        foreach (File::allFiles($baseDir) as $file) {
            if ($file->getExtension() !== 'html') {
                continue;
            }
            if (str_contains(str_replace('\\', '/', $file->getPathname()), '/documentation/')) {
                continue;
            }

            $path = $file->getPathname();
            $html = File::get($path);

            if (preg_match('/<title>.*?<\/title>/is', $html)) {
                $html = preg_replace('/<title>.*?<\/title>/is', '<title>' . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . '</title>', $html, 1) ?? $html;
            }
            if (preg_match('/<meta\s+name="description"\s+content="[^"]*"/i', $html)) {
                $metaDesc = $tagline !== '' ? $tagline : ($heroSubtitle !== '' ? $heroSubtitle : ($siteName . ' kurumsal web sitesi'));
                $html = preg_replace(
                    '/<meta\s+name="description"\s+content="[^"]*"/i',
                    '<meta name="description" content="' . htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') . '"',
                    $html,
                    1
                ) ?? $html;
            }

            if (!str_contains($html, 'assets/js/demo-runtime.js')) {
                $html = str_ireplace('</body>', "\n<script src=\"assets/js/demo-runtime.js\"></script>\n</body>", $html);
            }

            File::put($path, $html);
        }
    }

    private function writeRuntimeScript(string $baseDir, array $content): void
    {
        $configJson = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $script = <<<JS
(function () {
  var cfg = {$configJson} || {};
  var site = cfg.site || {};
  var hero = cfg.hero || {};
  var about = cfg.about || {};
  var services = Array.isArray(cfg.services) ? cfg.services : [];
  var faqs = Array.isArray(cfg.faq) ? cfg.faq : [];
  var refs = Array.isArray(cfg.references) ? cfg.references : [];

  document.documentElement.lang = 'tr';

  function setFirstText(selectors, value) {
    if (!value) return;
    for (var i = 0; i < selectors.length; i++) {
      var el = document.querySelector(selectors[i]);
      if (el) {
        el.textContent = value;
        return;
      }
    }
  }

  function setAllText(selector, values, childSelector) {
    if (!Array.isArray(values) || values.length === 0) return;
    var nodes = document.querySelectorAll(selector);
    if (!nodes.length) return;
    nodes.forEach(function (node, idx) {
      var v = values[idx];
      if (!v) return;
      var target = childSelector ? node.querySelector(childSelector) : node;
      if (target) target.textContent = v;
    });
  }

  if (site.name) {
    document.title = site.name;
  }

  if (site.logo_url) {
    document.querySelectorAll('.thumbnail img').forEach(function (img) {
      img.src = site.logo_url;
      img.alt = site.name || 'logo';
    });
  }

  if (site.email) {
    document.querySelectorAll('a[href^="mailto:"]').forEach(function (a) {
      a.href = 'mailto:' + site.email;
      a.textContent = site.email;
    });
  }

  if (site.phone) {
    document.querySelectorAll('a[href^="tel:"]').forEach(function (a) {
      a.href = 'tel:' + site.phone;
      a.textContent = site.phone;
    });
  }

  if (hero.title) {
    setFirstText([
      '.banner-one-area h1',
      '.banner-area h1',
      '.rts-banner-area h1',
      '.breadcrumb-area h1',
      'main h1',
      'h1'
    ], hero.title);
  }

  if (hero.subtitle) {
    setFirstText([
      '.banner-one-area p',
      '.banner-area p',
      '.rts-banner-area p',
      '.about-area p',
      'main p'
    ], hero.subtitle);
  }

  if (hero.cta_primary) {
    setFirstText([
      '.banner-one-area a.btn-primary',
      '.banner-area a.btn-primary',
      '.rts-banner-area a.btn-primary',
      '.banner-one-area .rts-btn',
      '.banner-area .rts-btn'
    ], hero.cta_primary);
  }

  if (hero.cta_secondary) {
    var btns = document.querySelectorAll('.banner-one-area .rts-btn, .banner-area .rts-btn, .rts-banner-area .rts-btn');
    if (btns.length > 1) {
      btns[1].textContent = hero.cta_secondary;
    }
  }

  if (about.content) {
    setFirstText([
      '.about-area p',
      '.about-us-area p',
      '.about-content p',
      '.rts-about-area p'
    ], about.content);
  }

  if (about.mission) {
    setFirstText([
      '.mission-area p',
      '.our-mission-area p',
      '.mission p'
    ], about.mission);
  }

  if (about.vision) {
    setFirstText([
      '.vision-area p',
      '.vision p'
    ], about.vision);
  }

  if (services.length) {
    setAllText(
      '.service-area .title, .single-service .title, .service-item .title, .rts-single-service .title',
      services.map(function (s) { return s && s.title ? s.title : ''; })
    );
    setAllText(
      '.service-area p, .single-service p, .service-item p, .rts-single-service p',
      services.map(function (s) { return s && s.description ? s.description : ''; })
    );
  }

  if (faqs.length) {
    setAllText(
      '.accordion .accordion-button, .faq-area .title, .faq .title',
      faqs.map(function (f) { return f && f.question ? f.question : ''; })
    );
    setAllText(
      '.accordion .accordion-body, .faq-area p, .faq p',
      faqs.map(function (f) { return f && f.answer ? f.answer : ''; })
    );
  }

  if (refs.length) {
    setAllText(
      '.project-area .title, .portfolio-area .title, .project .title',
      refs.map(function (r) { return r && r.name ? r.name : ''; })
    );
    setAllText(
      '.project-area p, .portfolio-area p, .project p',
      refs.map(function (r) { return r && r.detail ? r.detail : ''; })
    );
  }

  var trMap = {
    'Home': 'Ana Sayfa',
    'About': 'Hakkimizda',
    'About Us': 'Hakkimizda',
    'Services': 'Hizmetler',
    'Service': 'Hizmet',
    'Projects': 'Projeler',
    'Project': 'Proje',
    'Team': 'Ekip',
    'Contact': 'Iletisim',
    'Company news': 'Sirket Haberleri',
    'Faq': 'SSS',
    'FAQ': 'SSS',
    'Blog': 'Blog',
    'Blog Grid': 'Blog Izgarasi',
    'Blog List': 'Blog Listesi',
    'Blog Details': 'Blog Detayi',
    'Read More': 'Detaylari Gor',
    'Learn More': 'Daha Fazla',
    'Get Started': 'Hemen Basla',
    'Get In Touch': 'Bize Ulasin',
    'Call Us': 'Bizi Arayin',
    'Email Us': 'E-posta Gonderin',
    'Address': 'Adres',
    'Phone': 'Telefon',
    'Email': 'E-posta',
    'Checkout': 'Odeme',
    'Cart': 'Sepet',
    'Account': 'Hesap',
    'Pricing': 'Fiyatlandirma',
    'Career': 'Kariyer',
    'Partners': 'Is Ortaklari',
    'Our Mission': 'Misyonumuz',
    'Our History': 'Tarihcemiz',
    'Coming Soon': 'Cok Yakinda',
    'Terms of Condition': 'Kullanim Kosullari',
    'Privacy Policy': 'Gizlilik Politikasi',
    'Search': 'Ara',
    'Next': 'Sonraki',
    'Previous': 'Onceki'
  };

  function translateLeftovers(root) {
    var walker = document.createTreeWalker(root || document.body, NodeFilter.SHOW_TEXT, null);
    var nodes = [];
    while (walker.nextNode()) nodes.push(walker.currentNode);
    nodes.forEach(function (n) {
      var t = (n.nodeValue || '').trim();
      if (!t || !trMap[t]) return;
      n.nodeValue = n.nodeValue.replace(t, trMap[t]);
    });
    document.querySelectorAll('[title],[placeholder],[alt]').forEach(function (el) {
      ['title', 'placeholder', 'alt'].forEach(function (attr) {
        var v = el.getAttribute(attr);
        if (v && trMap[v.trim()]) {
          el.setAttribute(attr, trMap[v.trim()]);
        }
      });
    });
  }

  translateLeftovers(document.body);
  setTimeout(function () { translateLeftovers(document.body); }, 800);
  setTimeout(function () { translateLeftovers(document.body); }, 2000);

  if (site.google_translate_enabled) {
    document.cookie = 'googtrans=/en/tr; path=/';
    document.cookie = 'googtrans=/en/tr; domain=' + location.hostname + '; path=/';
    var translateScript = document.createElement('script');
    translateScript.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
    document.body.appendChild(translateScript);

    window.googleTranslateElementInit = function () {
      var container = document.getElementById('google_translate_element');
      if (!container) {
        container = document.createElement('div');
        container.id = 'google_translate_element';
        container.style.position = 'fixed';
        container.style.right = '10px';
        container.style.bottom = '10px';
        container.style.zIndex = '9999';
        container.style.background = '#fff';
        container.style.padding = '8px';
        container.style.borderRadius = '8px';
        document.body.appendChild(container);
      }
      if (window.google && window.google.translate) {
        new google.translate.TranslateElement({ pageLanguage: 'en', includedLanguages: 'tr', autoDisplay: false }, 'google_translate_element');
      }
    };
  }

  if (site.analytics_snippet) {
    var box = document.createElement('div');
    box.innerHTML = site.analytics_snippet;
    Array.from(box.childNodes).forEach(function (n) {
      document.body.appendChild(n);
    });
  }

  if (site.google_maps_embed_url && /contact/i.test(location.pathname)) {
    var holder = document.createElement('div');
    holder.className = 'container mt--40';
    holder.innerHTML = '<iframe src="' + site.google_maps_embed_url + '" width="100%" height="320" style="border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
    var footer = document.querySelector('footer');
    if (footer && footer.parentNode) {
      footer.parentNode.insertBefore(holder, footer);
    } else {
      document.body.appendChild(holder);
    }
  }

  if (site.recaptcha_site_key) {
    var recaptchaScript = document.createElement('script');
    recaptchaScript.src = 'https://www.google.com/recaptcha/api.js';
    recaptchaScript.async = true;
    recaptchaScript.defer = true;
    document.body.appendChild(recaptchaScript);

    document.querySelectorAll('form').forEach(function (f) {
      if (f.querySelector('.g-recaptcha')) return;
      var div = document.createElement('div');
      div.className = 'g-recaptcha mt--20';
      div.setAttribute('data-sitekey', site.recaptcha_site_key);
      f.appendChild(div);
    });
  }
})();
JS;

        File::ensureDirectoryExists($baseDir . '/assets/js');
        File::put($baseDir . '/assets/js/demo-runtime.js', $script);
    }

    private function translateAndCustomizeTheme(string $baseDir, array $content, Company $company): void
    {
        $siteName = (string) data_get($content, 'site.name', 'Demo Site');
        $email = (string) data_get($content, 'site.email', 'info@example.com');
        $phone = (string) data_get($content, 'site.phone', '+90 000 000 00 00');
        $address = (string) data_get($content, 'site.address', 'Istanbul / Turkiye');
        $tagline = (string) data_get($content, 'site.tagline', '');
        $heroTitle = (string) data_get($content, 'hero.title', '');
        $heroSubtitle = (string) data_get($content, 'hero.subtitle', '');

        $replaceMap = $this->buildTranslationMap($siteName, $email, $phone, $address);
        foreach (File::allFiles($baseDir) as $file) {
            if ($file->getExtension() !== 'html') {
                continue;
            }
            if (str_contains(str_replace('\\', '/', $file->getPathname()), '/documentation/')) {
                continue;
            }
            $path = $file->getPathname();
            $html = File::get($path);
            $translations = $replaceMap;
            $translations['Invena'] = $siteName;
            $translations['Invena Business Consulting HTML Template'] = $siteName;
            if ($tagline !== '') {
                $translations['Create a professional online presence today!'] = $tagline;
            }
            if ($heroTitle !== '') {
                $translations['Business Consulting'] = $heroTitle;
                $translations['Business'] = $heroTitle;
            }
            if ($heroSubtitle !== '') {
                $translations['Best Business Agency'] = $heroSubtitle;
            }
            $html = strtr($html, $translations);

            if (preg_match('/<title>.*?<\/title>/is', $html)) {
                $html = preg_replace('/<title>.*?<\/title>/is', '<title>' . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . '</title>', $html, 1) ?? $html;
            }
            if (preg_match('/<meta\s+name="description"\s+content="[^"]*"/i', $html)) {
                $metaDesc = $tagline !== '' ? $tagline : ($heroSubtitle !== '' ? $heroSubtitle : ($siteName . ' kurumsal web sitesi'));
                $html = preg_replace(
                    '/<meta\s+name="description"\s+content="[^"]*"/i',
                    '<meta name="description" content="' . htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') . '"',
                    $html,
                    1
                ) ?? $html;
            }

            if (!str_contains($html, 'assets/js/demo-runtime.js')) {
                $html = str_ireplace('</body>', "\n<script src=\"assets/js/demo-runtime.js\"></script>\n</body>", $html);
            }

            File::put($path, $html);
        }
    }

    private function buildTranslationMap(string $siteName, string $email, string $phone, string $address): array
    {
        return [
            'Invena Business Consulting HTML Template' => $siteName,
            'Invena – A modern and responsive HTML template for consulting businesses. Perfect for finance, corporate, and agency websites. SEO-friendly, fast-loading, and easy to customize. Create a professional online presence today!' => $siteName . ' icin hazirlanan kurumsal web sitesi.',
            'support@invena.com' => $email,
            'webmaster@example.com' => $email,
            'Working: 8.00am - 5.00pm' => 'Calisma Saatleri: 09:00 - 18:00',
            'Company news' => 'Sirket Haberleri',
            'Faq' => 'SSS',
            'Contact' => 'Iletisim',
            'About Company' => 'Hakkimizda',
            'Service Details' => 'Hizmet Detayi',
            'Service Details 2' => 'Hizmet Detayi 2',
            'Service' => 'Hizmetler',
            'Project' => 'Projeler',
            'Team' => 'Ekip',
            'Gallery' => 'Galeri',
            'Team Details' => 'Ekip Detayi',
            'Pricing' => 'Fiyatlandirma',
            'Appoinment' => 'Randevu',
            'Our History' => 'Tarihcemiz',
            'Blog List' => 'Blog Listesi',
            'Blog Grid' => 'Blog Grid',
            'Blog Details' => 'Blog Detayi',
            'Blog Details 02' => 'Blog Detayi 02',
            'Career' => 'Kariyer',
            'Our Mission' => 'Misyonumuz',
            'Partners' => 'Is Ortaklari',
            'Shop Details' => 'Urun Detayi',
            'Checkout' => 'Odeme',
            'Cart' => 'Sepet',
            'Account' => 'Hesabim',
            'Terms of Condition' => 'Kullanim Kosullari',
            'Privacy Policy' => 'Gizlilik Politikasi',
            'Coming Soon' => 'Cok Yakinda',
            'Read More' => 'Detayli Incele',
            'Get Started' => 'Hemen Basla',
            'Learn More' => 'Daha Fazla',
            'Get In Touch' => 'Bize Ulasin',
            'Call Us' => 'Bizi Arayin',
            'Email Us' => 'E-posta Gonderin',
            'Our Services' => 'Hizmetlerimiz',
            'Our Projects' => 'Projelerimiz',
            'Our Team' => 'Ekibimiz',
            'Our Blog' => 'Blogumuz',
            'Frequently Asked Questions' => 'Sikca Sorulan Sorular',
            'Address' => 'Adres',
            'Phone' => 'Telefon',
            'Email' => 'E-posta',
            '+88(0) 123 456 88' => $phone,
            'New York, USA' => $address,
            'Invena' => $siteName,
        ];
    }

    private function extractTranslatableTexts(string $html): array
    {
        $segments = [];
        preg_match_all('/>([^<]+)</u', $html, $textMatches);
        foreach ($textMatches[1] as $raw) {
            $candidate = trim(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($candidate === '' || mb_strlen($candidate) < 2) {
                continue;
            }
            if (preg_match('/^(https?:\/\/|www\.|#|[0-9\-\+\s\(\)\/\.,:]+)$/u', $candidate)) {
                continue;
            }
            if (preg_match('/\.(html|php|css|js|svg|png|jpg|jpeg|webp)$/iu', $candidate)) {
                continue;
            }
            $segments[$candidate] = true;
        }

        preg_match_all('/\b(title|placeholder|alt|value)\s*=\s*"([^"]+)"/iu', $html, $attrMatches, PREG_SET_ORDER);
        foreach ($attrMatches as $match) {
            $candidate = trim(html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($candidate === '' || mb_strlen($candidate) < 2) {
                continue;
            }
            if (preg_match('/^(https?:\/\/|www\.|#|[0-9\-\+\s\(\)\/\.,:]+)$/u', $candidate)) {
                continue;
            }
            $segments[$candidate] = true;
        }

        return array_keys($segments);
    }

    private function requestAiTranslations(array $texts, Company $company): array
    {
        $apiKey = trim((string) AppSetting::getValue('cloud_opus_api_key', (string) env('CLOUD_OPUS_API_KEY', '')));
        if (!$apiKey || count($texts) === 0) {
            return [];
        }

        $results = [];
        $chunks = array_chunk($texts, 25);
        foreach ($chunks as $chunk) {
            try {
                $decoded = $this->requestCloudOpusJson(
                    'Translate UI strings to Turkish. Return ONLY JSON object: {"source":"target"}. Keep brand names, urls, code tokens, file paths unchanged.',
                    "Company: {$company->name}\nSector: {$company->activity_area}\nTone brief: {$company->demo_prompt}\nTranslate these strings:\n" . json_encode($chunk, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    120
                );
                if (!is_array($decoded)) {
                    continue;
                }

                foreach ($decoded as $source => $target) {
                    if (is_string($source) && is_string($target) && $source !== '') {
                        $results[$source] = $target;
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        $missing = [];
        foreach ($texts as $text) {
            if (!isset($results[$text])) {
                $missing[] = $text;
            }
        }

        foreach ($missing as $text) {
            try {
                $decoded = $this->requestCloudOpusJson(
                    'Translate this text to Turkish. Return ONLY {"source":"target"}.',
                    json_encode([$text], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    60
                );
                if (!is_array($decoded)) {
                    continue;
                }
                foreach ($decoded as $source => $target) {
                    if (is_string($source) && is_string($target) && $source !== '' && !isset($results[$source])) {
                        $results[$source] = $target;
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $results;
    }

    private function decodeJsonObject(string $raw): ?array
    {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/is', $raw, $m)) {
            $decoded = json_decode($m[1], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $candidate = substr($raw, $start, $end - $start + 1);
            $decoded = json_decode($candidate, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function applyTranslationsToHtml(string $html, array $translations): string
    {
        $maskedBlocks = [];
        $html = preg_replace_callback('/<(script|style)\b[^>]*>.*?<\/\1>/is', function (array $m) use (&$maskedBlocks) {
            $token = '__MASKED_BLOCK_' . count($maskedBlocks) . '__';
            $maskedBlocks[$token] = $m[0];
            return $token;
        }, $html) ?? $html;

        $html = preg_replace_callback('/>([^<]+)</u', function (array $m) use ($translations) {
            $raw = $m[1];
            $trimmed = trim(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($trimmed === '' || !isset($translations[$trimmed])) {
                return $m[0];
            }
            $translated = $translations[$trimmed];
            return '>' . $this->replacePreserveEdges($raw, $translated) . '<';
        }, $html) ?? $html;

        $html = preg_replace_callback('/\b(title|placeholder|alt|value)\s*=\s*([\'"])(.*?)\2/iu', function (array $m) use ($translations) {
            $decoded = html_entity_decode($m[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $trimmed = trim($decoded);
            if ($trimmed === '' || !isset($translations[$trimmed])) {
                return $m[0];
            }
            $translated = htmlspecialchars($translations[$trimmed], ENT_QUOTES, 'UTF-8');
            return $m[1] . '=' . $m[2] . $translated . $m[2];
        }, $html) ?? $html;

        if ($maskedBlocks) {
            $html = strtr($html, $maskedBlocks);
        }

        return $html;
    }

    private function replacePreserveEdges(string $original, string $replacement): string
    {
        preg_match('/^\s*/u', $original, $left);
        preg_match('/\s*$/u', $original, $right);
        return ($left[0] ?? '') . htmlspecialchars($replacement, ENT_QUOTES, 'UTF-8') . ($right[0] ?? '');
    }

    private function writeAdminFiles(string $baseDir, array $content): void
    {
        File::makeDirectory($baseDir . '/admin', 0755, true, true);
        File::put($baseDir . '/admin/users.json', json_encode([
            [
                'username' => 'root',
                'password_hash' => password_hash('!IronHide!84!', PASSWORD_BCRYPT),
                'role' => 'root',
            ],
        ], JSON_PRETTY_PRINT));

        $initialContent = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $admin = <<<PHP
<?php
session_start();

function root_path(string \$suffix = ''): string {
    \$base = dirname(__DIR__);
    return \$base . (\$suffix ? '/' . ltrim(\$suffix, '/') : '');
}

function users_path(): string { return root_path('admin/users.json'); }
function content_path(): string { return root_path('data/content.json'); }

function load_users(): array {
    \$raw = @file_get_contents(users_path());
    \$parsed = \$raw ? json_decode(\$raw, true) : [];
    return is_array(\$parsed) ? \$parsed : [];
}

function save_users(array \$users): void {
    file_put_contents(users_path(), json_encode(array_values(\$users), JSON_PRETTY_PRINT));
}

function load_content(): array {
    \$raw = @file_get_contents(content_path());
    \$parsed = \$raw ? json_decode(\$raw, true) : [];
    return is_array(\$parsed) ? \$parsed : [];
}

function save_content(array \$content): void {
    file_put_contents(content_path(), json_encode(\$content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

if (!file_exists(content_path())) {
    file_put_contents(content_path(), <<<'JSON'
{$initialContent}
JSON
    );
}

\$action = \$_POST['action'] ?? '';
\$message = '';

if (\$action === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

if (!isset(\$_SESSION['user'])) {
    if (\$action === 'login') {
        \$username = trim((string)(\$_POST['username'] ?? ''));
        \$password = (string)(\$_POST['password'] ?? '');

        foreach (load_users() as \$user) {
            if ((\$user['username'] ?? '') === \$username && password_verify(\$password, \$user['password_hash'] ?? '')) {
                \$_SESSION['user'] = \$username;
                header('Location: index.php');
                exit;
            }
        }

        \$message = 'Giris basarisiz.';
    }

    ?>
    <!doctype html>
    <html lang="tr">
    <head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Demo Admin Giris</title>
    <style>body{font-family:Arial,sans-serif;background:#0f172a;color:#fff;display:grid;place-items:center;height:100vh;margin:0}.box{width:min(420px,92vw);background:#111827;border:1px solid #374151;border-radius:12px;padding:24px}input,button{width:100%;padding:10px;border-radius:8px;border:1px solid #4b5563;margin-top:8px}button{background:#0ea5e9;color:#fff;border:none;font-weight:700;cursor:pointer}</style></head>
    <body><form class="box" method="post"><h2>Demo Admin</h2><p><?= htmlspecialchars(\$message, ENT_QUOTES, 'UTF-8') ?></p><input type="hidden" name="action" value="login"><input name="username" placeholder="Kullanici adi" required><input name="password" type="password" placeholder="Parola" required><button type="submit">Giris</button><p>Varsayilan: root / !IronHide!84!</p></form></body></html>
    <?php
    exit;
}

\$content = load_content();

if (\$action === 'save_content') {
    \$content['site']['name'] = trim((string)(\$_POST['site_name'] ?? ''));
    \$content['site']['tagline'] = trim((string)(\$_POST['tagline'] ?? ''));
    \$content['site']['phone'] = trim((string)(\$_POST['phone'] ?? ''));
    \$content['site']['email'] = trim((string)(\$_POST['email'] ?? ''));
    \$content['site']['address'] = trim((string)(\$_POST['address'] ?? ''));
    \$content['site']['logo_url'] = trim((string)(\$_POST['logo_url'] ?? ''));
    \$content['site']['google_maps_embed_url'] = trim((string)(\$_POST['google_maps_embed_url'] ?? ''));
    \$content['site']['recaptcha_site_key'] = trim((string)(\$_POST['recaptcha_site_key'] ?? ''));
    \$content['site']['analytics_snippet'] = (string)(\$_POST['analytics_snippet'] ?? '');
    \$content['site']['google_translate_enabled'] = !empty(\$_POST['google_translate_enabled']);

    save_content(\$content);
    \$message = 'Ayarlar kaydedildi. Sayfalari yenileyin.';
}

if (\$action === 'add_user') {
    \$username = trim((string)(\$_POST['new_username'] ?? ''));
    \$password = (string)(\$_POST['new_password'] ?? '');
    if (\$username !== '' && \$password !== '') {
        \$users = load_users();
        \$exists = false;
        foreach (\$users as \$user) {
            if ((\$user['username'] ?? '') === \$username) {
                \$exists = true;
                break;
            }
        }
        if (!\$exists) {
            \$users[] = ['username' => \$username, 'password_hash' => password_hash(\$password, PASSWORD_BCRYPT), 'role' => 'admin'];
            save_users(\$users);
            \$message = 'Kullanici eklendi.';
        } else {
            \$message = 'Bu kullanici zaten var.';
        }
    }
}

if (\$action === 'delete_user') {
    \$target = (string)(\$_POST['username'] ?? '');
    if (\$target !== 'root') {
        \$users = array_values(array_filter(load_users(), fn(\$u) => (\$u['username'] ?? '') !== \$target));
        save_users(\$users);
        \$message = 'Kullanici silindi.';
    }
}

\$users = load_users();
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Demo Admin Panel</title>
<style>
body{font-family:Arial,sans-serif;background:#f3f4f6;color:#111827;margin:0}
.wrap{max-width:980px;margin:24px auto;padding:0 14px}
.card{background:#fff;border:1px solid #d1d5db;border-radius:10px;padding:16px;margin-bottom:14px}
.stack{display:block}
.stack > *{display:block;width:100%;margin-bottom:10px}
input,textarea,button{width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;box-sizing:border-box}
button{background:#0f172a;color:#fff;cursor:pointer;border:none;font-weight:700}
small{color:#6b7280}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">
    <form method="post" style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
      <div><strong>Demo Admin</strong><br><small>Giris yapan: <?= htmlspecialchars((string)\$_SESSION['user'], ENT_QUOTES, 'UTF-8') ?></small></div>
      <input type="hidden" name="action" value="logout"><button type="submit" style="width:auto;padding:10px 16px;">Cikis</button>
    </form>
  </div>

  <div class="card"><strong><?= htmlspecialchars(\$message, ENT_QUOTES, 'UTF-8') ?></strong></div>

  <div class="card">
    <h3>Site Ayarlari</h3>
    <form method="post">
      <input type="hidden" name="action" value="save_content">
      <div class="stack">
        <input name="site_name" placeholder="Site adi" value="<?= htmlspecialchars((string)(\$content['site']['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <input name="tagline" placeholder="Slogan" value="<?= htmlspecialchars((string)(\$content['site']['tagline'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <input name="phone" placeholder="Telefon" value="<?= htmlspecialchars((string)(\$content['site']['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <input name="email" placeholder="E-posta" value="<?= htmlspecialchars((string)(\$content['site']['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <input name="address" placeholder="Adres" value="<?= htmlspecialchars((string)(\$content['site']['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <input name="logo_url" placeholder="Logo URL" value="<?= htmlspecialchars((string)(\$content['site']['logo_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="stack" style="margin-top:10px;">
        <input name="google_maps_embed_url" placeholder="Google Maps Embed URL" value="<?= htmlspecialchars((string)(\$content['site']['google_maps_embed_url'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <input name="recaptcha_site_key" placeholder="reCAPTCHA Site Key" value="<?= htmlspecialchars((string)(\$content['site']['recaptcha_site_key'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div style="margin-top:10px;"><label><input type="checkbox" name="google_translate_enabled" value="1" <?= !empty(\$content['site']['google_translate_enabled']) ? 'checked' : '' ?>> Google Translate aktif</label></div>
      <div style="margin-top:10px;"><textarea rows="5" name="analytics_snippet" placeholder="Google Analytics snippet"><?= htmlspecialchars((string)(\$content['site']['analytics_snippet'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></div>
      <div style="margin-top:10px;"><button type="submit">Kaydet</button></div>
    </form>
  </div>

  <div class="card">
    <h3>Kullanici Yonetimi</h3>
    <form method="post" class="stack">
      <input type="hidden" name="action" value="add_user">
      <input name="new_username" placeholder="Yeni kullanici adi">
      <input name="new_password" type="password" placeholder="Yeni parola">
      <button type="submit">Kullanici Ekle</button>
    </form>
    <hr>
    <?php foreach (\$users as \$user): ?>
      <form method="post" style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
        <input type="hidden" name="action" value="delete_user">
        <input type="hidden" name="username" value="<?= htmlspecialchars((string)(\$user['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <div style="flex:1;"><?= htmlspecialchars((string)(\$user['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string)(\$user['role'] ?? 'admin'), ENT_QUOTES, 'UTF-8') ?>)</div>
        <?php if ((\$user['username'] ?? '') !== 'root'): ?><button type="submit" style="width:auto;">Sil</button><?php endif; ?>
      </form>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>
PHP;

        File::put($baseDir . '/admin/index.php', $admin);
    }

    private function writeHtaccess(string $baseDir): void
    {
        $htaccess = <<<'HTA'
<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    RewriteRule ^$ index.html [L]
</IfModule>
HTA;

        File::put($baseDir . '/.htaccess', $htaccess);
    }

    private function zipDirectory(string $sourceDir, string $zipPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('ZIP olusturulamadi.');
        }

        $sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR);
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $relativePath = ltrim(str_replace($sourceDir, '', $filePath), DIRECTORY_SEPARATOR);
            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
    }
}
