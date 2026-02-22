<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'smtp_host' => AppSetting::getValue('smtp_host', ''),
            'smtp_port' => AppSetting::getValue('smtp_port', '587'),
            'smtp_username' => AppSetting::getValue('smtp_username', ''),
            'smtp_password' => AppSetting::getValue('smtp_password', ''),
            'smtp_encryption' => AppSetting::getValue('smtp_encryption', 'tls'),
            'smtp_from_address' => AppSetting::getValue('smtp_from_address', ''),
            'smtp_from_name' => AppSetting::getValue('smtp_from_name', 'BT Places'),
            'follow_up_days' => AppSetting::getValue('follow_up_days', (string) env('FOLLOW_UP_DAYS', '10')),
            'logo_url' => AppSetting::getValue('logo_url', ''),
            'logo_height' => AppSetting::getValue('logo_height', '40'),
            'login_logo_url' => AppSetting::getValue('login_logo_url', ''),
            'login_logo_height' => AppSetting::getValue('login_logo_height', '40'),
            'cloud_opus_api_key' => AppSetting::getValue('cloud_opus_api_key', (string) env('CLOUD_OPUS_API_KEY', '')),
            'cloud_opus_model' => AppSetting::getValue('cloud_opus_model', (string) env('CLOUD_OPUS_MODEL', 'claude-opus-4-1-20250805')),
            'cloud_opus_base_url' => AppSetting::getValue('cloud_opus_base_url', (string) env('CLOUD_OPUS_BASE_URL', 'https://api.anthropic.com/v1/messages')),
            'cloud_opus_max_tokens' => AppSetting::getValue('cloud_opus_max_tokens', (string) env('CLOUD_OPUS_MAX_TOKENS', '2400')),
        ];

        $users = User::query()->orderBy('created_at')->get();

        return view('settings.index', compact('settings', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'smtp_host' => ['required', 'string', 'max:190'],
            'smtp_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:190'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_encryption' => ['nullable', 'string', 'max:20'],
            'smtp_from_address' => ['required', 'email', 'max:190'],
            'smtp_from_name' => ['nullable', 'string', 'max:120'],
            'follow_up_days' => ['required', 'integer', 'min:1', 'max:60'],
            'cloud_opus_api_key' => ['nullable', 'string', 'max:300'],
            'cloud_opus_model' => ['required', 'string', 'max:120'],
            'cloud_opus_base_url' => ['required', 'url', 'max:255'],
            'cloud_opus_max_tokens' => ['required', 'integer', 'min:256', 'max:8192'],
        ]);

        foreach ($data as $key => $value) {
            AppSetting::setValue($key, is_null($value) ? null : (string) $value);
        }

        return redirect()->route('settings.index')->with('status', 'SMTP, takip ve Cloud Opus ayarlari kaydedildi.');
    }

    public function uploadLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'logo_height' => ['nullable', 'integer', 'min:20', 'max:120'],
        ]);

        $file = $request->file('logo');
        File::ensureDirectoryExists(public_path('uploads'));
        $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads'), $filename);

        AppSetting::setValue('logo_url', 'uploads/' . $filename);

        if ($request->filled('logo_height')) {
            AppSetting::setValue('logo_height', (string) $request->input('logo_height'));
        }

        return redirect()->route('settings.index')->with('status', 'Logo başarıyla yüklendi.');
    }

    public function uploadLoginLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'login_logo' => ['required', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'login_logo_height' => ['nullable', 'integer', 'min:20', 'max:120'],
        ]);

        $file = $request->file('login_logo');
        File::ensureDirectoryExists(public_path('uploads'));
        $filename = 'login_logo_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads'), $filename);

        AppSetting::setValue('login_logo_url', 'uploads/' . $filename);

        if ($request->filled('login_logo_height')) {
            AppSetting::setValue('login_logo_height', (string) $request->input('login_logo_height'));
        }

        return redirect()->route('settings.index')->with('status', 'Giriş ekranı logosu kaydedildi.');
    }

    public function testCloudOpus(): RedirectResponse
    {
        $apiKey = trim((string) AppSetting::getValue('cloud_opus_api_key', (string) env('CLOUD_OPUS_API_KEY', '')));
        $model = trim((string) AppSetting::getValue('cloud_opus_model', (string) env('CLOUD_OPUS_MODEL', 'claude-opus-4-1-20250805')));
        $baseUrl = trim((string) AppSetting::getValue('cloud_opus_base_url', (string) env('CLOUD_OPUS_BASE_URL', 'https://api.anthropic.com/v1/messages')));

        if ($apiKey === '') {
            return redirect()->route('settings.index')->with('status', 'Cloud Opus testi basarisiz: API anahtari tanimli degil.');
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post($baseUrl, [
                    'model' => $model,
                    'max_tokens' => 32,
                    'messages' => [
                        ['role' => 'user', 'content' => 'ping'],
                    ],
                ]);

            if (!$response->successful()) {
                $code = $response->status();
                $msg = (string) data_get($response->json(), 'error.message', 'Bilinmeyen hata');
                return redirect()->route('settings.index')->with('status', "Cloud Opus testi basarisiz ({$code}): {$msg}");
            }

            $respModel = (string) data_get($response->json(), 'model', $model);
            return redirect()->route('settings.index')->with('status', "Cloud Opus baglantisi basarili. Model: {$respModel}");
        } catch (\Throwable $e) {
            return redirect()->route('settings.index')->with('status', 'Cloud Opus testi basarisiz: ' . $e->getMessage());
        }
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'role' => ['required', 'in:admin,sales,operator'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return redirect()->route('settings.index')->with('status', 'Kullanıcı eklendi.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'role' => ['required', 'in:admin,sales,operator'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('settings.index')->with('status', 'Kullanıcı güncellendi.');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('settings.index')->with('status', 'Kendi hesabınızı silemezsiniz.');
        }

        if (User::count() <= 1) {
            return redirect()->route('settings.index')->with('status', 'Sistemden son kullanıcı silinemez.');
        }

        $user->delete();

        return redirect()->route('settings.index')->with('status', 'Kullanıcı silindi.');
    }
}
