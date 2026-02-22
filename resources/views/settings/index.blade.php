@extends('layouts.app')
@section('title', 'Ayarlar')
@section('page-title', 'Ayarlar')

@section('content')
@php
    $sidebarLogoPreview = \App\Models\AppSetting::getAssetUrl('logo_url');
    $loginLogoPreview = \App\Models\AppSetting::getAssetUrl('login_logo_url');
@endphp
<div class="mx-auto max-w-4xl space-y-6">
    {{-- SMTP Settings --}}
    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-100 text-brand-600">
                <i data-lucide="mail" class="h-5 w-5"></i>
            </div>
            <div>
                <h3 class="text-base font-semibold text-gray-800">SMTP ve Takip Ayarları</h3>
                <p class="text-xs text-gray-500">E-posta gönderimi ve otomatik takip ayarları</p>
            </div>
        </div>
        <div class="p-6">
            <form method="post" action="{{ route('settings.store') }}" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">SMTP Host</label>
                    <div class="relative">
                        <i data-lucide="server" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                        <input name="smtp_host" placeholder="smtp.gmail.com" value="{{ old('smtp_host', $settings['smtp_host']) }}" required
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">SMTP Port</label>
                    <div class="relative">
                        <i data-lucide="hash" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                        <input name="smtp_port" placeholder="587" value="{{ old('smtp_port', $settings['smtp_port']) }}" required
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">SMTP Kullanıcı</label>
                    <div class="relative">
                        <i data-lucide="user" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                        <input name="smtp_username" placeholder="user@example.com" value="{{ old('smtp_username', $settings['smtp_username']) }}"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">SMTP Şifre</label>
                    <div class="relative">
                        <i data-lucide="lock" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="smtp_password" placeholder="••••••••" value="{{ old('smtp_password', $settings['smtp_password']) }}"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Şifreleme</label>
                    <select name="smtp_encryption"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                        <option value="" {{ old('smtp_encryption', $settings['smtp_encryption']) === '' ? 'selected' : '' }}>Yok</option>
                        <option value="tls" {{ old('smtp_encryption', $settings['smtp_encryption']) === 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ old('smtp_encryption', $settings['smtp_encryption']) === 'ssl' ? 'selected' : '' }}>SSL</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Gönderen E-posta</label>
                    <div class="relative">
                        <i data-lucide="at-sign" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                        <input name="smtp_from_address" placeholder="info@example.com" value="{{ old('smtp_from_address', $settings['smtp_from_address']) }}" required
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Gönderen Adı</label>
                    <div class="relative">
                        <i data-lucide="user-circle" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                        <input name="smtp_from_name" placeholder="BT Places" value="{{ old('smtp_from_name', $settings['smtp_from_name']) }}"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Takip Günü (default 10)</label>
                    <div class="relative">
                        <i data-lucide="calendar-days" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                        <input name="follow_up_days" placeholder="10" value="{{ old('follow_up_days', $settings['follow_up_days']) }}" required
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Cloud Opus API Key</label>
                    <div class="relative">
                        <i data-lucide="key-round" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                        <input name="cloud_opus_api_key" placeholder="sk-ant-..." value="{{ old('cloud_opus_api_key', $settings['cloud_opus_api_key']) }}"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Cloud Opus Model</label>
                    <div class="relative">
                        <i data-lucide="bot" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                        <input name="cloud_opus_model" required placeholder="claude-opus-4-1-20250805" value="{{ old('cloud_opus_model', $settings['cloud_opus_model']) }}"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Cloud Opus Endpoint</label>
                    <div class="relative">
                        <i data-lucide="link" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                        <input name="cloud_opus_base_url" required placeholder="https://api.anthropic.com/v1/messages" value="{{ old('cloud_opus_base_url', $settings['cloud_opus_base_url']) }}"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Cloud Opus Max Tokens</label>
                    <div class="relative">
                        <i data-lucide="hash" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                        <input name="cloud_opus_max_tokens" type="number" min="256" max="8192" required value="{{ old('cloud_opus_max_tokens', $settings['cloud_opus_max_tokens']) }}"
                               class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100">
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="btn-primary flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Ayarları Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Logo Ayarları --}}
    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <i data-lucide="image" class="h-5 w-5"></i>
            </div>
            <div>
                <h3 class="text-base font-semibold text-gray-800">Logo Ayarları</h3>
                <p class="text-xs text-gray-500">Sidebar'da görünecek logo ve boyut ayarları</p>
            </div>
        </div>
        <div class="p-6">
            <form method="post" action="{{ route('settings.upload-logo') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-end">
                    {{-- Current Logo Preview --}}
                    <div class="flex-shrink-0">
                        <label class="mb-1.5 block text-xs font-medium text-gray-500">Mevcut Logo</label>
                        <div class="flex h-24 w-48 items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50">
                            @if($sidebarLogoPreview)
                                <img src="{{ $sidebarLogoPreview }}" alt="Logo" style="height: {{ $settings['logo_height'] ?: 40 }}px;" class="max-w-full object-contain">
                            @else
                                <div class="flex flex-col items-center gap-1 text-gray-400">
                                    <i data-lucide="image-off" class="h-6 w-6"></i>
                                    <span class="text-xs">Logo yüklenmemiş</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- Upload + Height --}}
                    <div class="flex-1 space-y-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-500">Yeni Logo Yükle</label>
                            <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-600 hover:file:bg-brand-100 focus:outline-none">
                            <p class="mt-1 text-xs text-gray-400">PNG, JPG, SVG veya WebP. Maks 2MB.</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-500">Logo Yüksekliği: <span id="logo-height-val" class="font-semibold text-brand-600">{{ $settings['logo_height'] ?: 40 }}px</span></label>
                            <input type="range" name="logo_height" min="20" max="120" step="2" value="{{ $settings['logo_height'] ?: 40 }}"
                                   oninput="document.getElementById('logo-height-val').textContent = this.value + 'px'"
                                   class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200 accent-brand-600">
                            <div class="mt-1 flex justify-between text-[10px] text-gray-400">
                                <span>20px</span>
                                <span>120px</span>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                    <i data-lucide="upload" class="h-4 w-4"></i>
                    Logoyu Kaydet
                </button>
            </form>

            <div class="my-6 border-t border-gray-100"></div>

            <form method="post" action="{{ route('settings.upload-login-logo') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div class="mb-2 flex items-center gap-2">
                    <i data-lucide="log-in" class="h-4 w-4 text-brand-500"></i>
                    <h4 class="text-sm font-semibold text-gray-800">Giriş Ekranı Logosunu Ayrı Yönet</h4>
                </div>
                <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-end">
                    <div class="flex-shrink-0">
                        <label class="mb-1.5 block text-xs font-medium text-gray-500">Giriş Ekranı Mevcut Logo</label>
                        <div class="flex h-24 w-48 items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50">
                            @if($loginLogoPreview)
                                <img src="{{ $loginLogoPreview }}" alt="Login Logo" style="height: {{ $settings['login_logo_height'] ?: 40 }}px;" class="max-w-full object-contain">
                            @else
                                <div class="flex flex-col items-center gap-1 text-gray-400">
                                    <i data-lucide="image-off" class="h-6 w-6"></i>
                                    <span class="text-xs">Ayrı login logosu yok</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex-1 space-y-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-500">Giriş Ekranı Yeni Logo Yükle</label>
                            <input type="file" name="login_logo" accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                   class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-600 hover:file:bg-brand-100 focus:outline-none">
                            <p class="mt-1 text-xs text-gray-400">PNG, JPG, SVG veya WebP. Maks 2MB.</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-500">Login Logo Yüksekliği: <span id="login-logo-height-val" class="font-semibold text-brand-600">{{ $settings['login_logo_height'] ?: 40 }}px</span></label>
                            <input type="range" name="login_logo_height" min="20" max="120" step="2" value="{{ $settings['login_logo_height'] ?: 40 }}"
                                   oninput="document.getElementById('login-logo-height-val').textContent = this.value + 'px'"
                                   class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-gray-200 accent-brand-600">
                            <div class="mt-1 flex justify-between text-[10px] text-gray-400">
                                <span>20px</span>
                                <span>120px</span>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                    <i data-lucide="upload" class="h-4 w-4"></i>
                    Login Logosunu Kaydet
                </button>
            </form>
        </div>
    </div>

    {{-- Kullanıcı Yönetimi --}}
    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                <i data-lucide="users" class="h-5 w-5"></i>
            </div>
            <div>
                <h3 class="text-base font-semibold text-gray-800">Kullanıcı Yönetimi</h3>
                <p class="text-xs text-gray-500">Yeni kullanıcı ekleyin, rollerini ve şifrelerini yönetin</p>
            </div>
        </div>
        <div class="space-y-6 p-6">
            <form method="post" action="{{ route('settings.users.store') }}" class="grid grid-cols-1 gap-4 rounded-xl border border-gray-100 bg-gray-50/60 p-4 md:grid-cols-4">
                @csrf
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Ad Soyad</label>
                    <input name="name" required class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">E-posta</label>
                    <input name="email" type="email" required class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Şifre</label>
                    <input name="password" type="password" required class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium text-gray-500">Rol</label>
                    <select name="role" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                        <option value="admin">admin</option>
                        <option value="sales">sales</option>
                        <option value="operator" selected>operator</option>
                    </select>
                </div>
                <div class="md:col-span-4">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        <i data-lucide="user-plus" class="h-4 w-4"></i>
                        Kullanıcı Ekle
                    </button>
                </div>
            </form>

            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <th class="px-4 py-3">Kullanıcı</th>
                            <th class="px-4 py-3">E-posta</th>
                            <th class="px-4 py-3">Rol</th>
                            <th class="px-4 py-3">Yeni Şifre</th>
                            <th class="px-4 py-3">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-4 py-3">
                                    <input form="user-update-{{ $user->id }}" name="name" value="{{ $user->name }}" required class="w-full min-w-[170px] rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                </td>
                                <td class="px-4 py-3">
                                    <input form="user-update-{{ $user->id }}" name="email" type="email" value="{{ $user->email }}" required class="w-full min-w-[230px] rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                </td>
                                <td class="px-4 py-3">
                                    <select form="user-update-{{ $user->id }}" name="role" class="w-full min-w-[130px] rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>admin</option>
                                        <option value="sales" {{ $user->role === 'sales' ? 'selected' : '' }}>sales</option>
                                        <option value="operator" {{ $user->role === 'operator' ? 'selected' : '' }}>operator</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input form="user-update-{{ $user->id }}" name="password" type="password" placeholder="Boş bırakırsan değişmez" class="w-full min-w-[190px] rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <form id="user-update-{{ $user->id }}" method="post" action="{{ route('settings.users.update', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-700">
                                                <i data-lucide="save" class="h-3.5 w-3.5"></i>
                                                Kaydet
                                            </button>
                                        </form>
                                        <form method="post" action="{{ route('settings.users.destroy', $user) }}" onsubmit="return confirm('Bu kullanıcı silinsin mi?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100">
                                                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                                Sil
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">Kayıtlı kullanıcı bulunmuyor.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Cloud Opus Test --}}
    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                <i data-lucide="bot" class="h-5 w-5"></i>
            </div>
            <div>
                <h3 class="text-base font-semibold text-gray-800">Cloud Opus Bağlantı Testi</h3>
                <p class="text-xs text-gray-500">Model erişimi ve API bağlantısını doğrulayın</p>
            </div>
        </div>
        <div class="p-6">
            <form method="post" action="{{ route('settings.test-cloud-opus') }}">
                @csrf
                <button type="submit" class="btn-primary flex items-center gap-2 rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                    <i data-lucide="zap" class="h-4 w-4"></i>
                    Cloud Opus Bağlantısını Test Et
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>document.addEventListener('DOMContentLoaded', function() { lucide.createIcons(); });</script>
@endsection