<!doctype html>
<html lang="tr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BT Places — Giriş</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                borderRadius: {
                    none: '0', sm: '0', DEFAULT: '0', md: '0', lg: '0', xl: '0', '2xl': '0', '3xl': '0',
                    full: '9999px'
                },
                extend: {
                    colors: {
                        brand: { 50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="h-full bg-gradient-to-br from-gray-50 via-white to-brand-50/40 font-sans text-gray-800 antialiased">
@php
    $loginLogoUrl = \App\Models\AppSetting::getAssetUrl('login_logo_url');
    $loginLogoHeight = \App\Models\AppSetting::getValue('login_logo_height', '40');
    $logoUrl = $loginLogoUrl !== '' ? $loginLogoUrl : \App\Models\AppSetting::getAssetUrl('logo_url');
    $logoHeight = $loginLogoUrl !== '' ? $loginLogoHeight : \App\Models\AppSetting::getValue('logo_height', '40');
@endphp

<div class="flex min-h-full items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
    <div class="w-full max-w-md border-t-4 border-t-brand-600 border border-gray-100 bg-white p-7 shadow-md sm:p-8">
        <div class="mb-6 flex justify-center">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo" style="height: {{ $logoHeight }}px;" class="max-w-full object-contain">
            @else
                <span class="text-2xl font-bold tracking-tight text-gray-900">BT Places</span>
            @endif
        </div>

        <div class="mb-6 text-center">
            <h1 class="text-xl font-semibold text-gray-900">Kullanıcı Girişi</h1>
            <p class="mt-1 text-sm text-gray-500">Devam etmek için hesabınızla giriş yapın</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 border-l-4 border-l-red-500 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="post" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">Kullanıcı adı veya E-posta</label>
                <div class="relative">
                    <i data-lucide="mail" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100"
                           placeholder="root veya ornek@btplaces.com">
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-medium text-gray-500">Şifre</label>
                <div class="relative">
                    <i data-lucide="lock" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                    <input type="password" name="password" required
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-100"
                           placeholder="••••••••">
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                Beni hatırla
            </label>

            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                <i data-lucide="log-in" class="h-4 w-4"></i>
                Giriş Yap
            </button>
        </form>
    </div>
</div>

<script>document.addEventListener('DOMContentLoaded', () => lucide.createIcons());</script>
</body>
</html>
