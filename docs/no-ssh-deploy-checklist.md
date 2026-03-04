# No-SSH Deployment Checklist (cPanel)

## 1) Dosya yukleme

Laravel projesinin root dizinine su dosyalari yukleyin:

- `app/Http/Controllers/*`
- `app/Models/*`
- `database/migrations/*`
- `resources/views/layouts/app.blade.php`
- `resources/views/dashboard.blade.php`
- `resources/views/companies/index.blade.php`
- `resources/views/import-logs/index.blade.php`
- `resources/views/leads/index.blade.php`
- `resources/views/settings/index.blade.php`
- `app/Http/Controllers/DemoProjectController.php`
- `app/Models/DemoProject.php`
- `routes/web.php`

## 2) .env ayarlari

`APP_URL`, `DB_*`, `MAIL_*`, `GOOGLE_PLACES_API_KEY` alanlarini doldurun.

## 3) Migration (SSH yoksa)

- cPanel Terminal varsa: `php artisan migrate`
- Terminal yoksa: `db/schema.sql` dosyasini phpMyAdmin ile import edin (place_import_logs, app_settings, demo_projects dahil).

## 4) Login

Laravel Breeze kuruluysa `/login` ekranini kullanin.
Breeze yoksa once panelden dependency kurulumu gerekir.

## 5) Ilk test

- `/dashboard`
- `/companies`
- `/leads`
- `/settings`
- `/reports`
- `/import-logs`

Beklenen: Firma ekleme ve website yoksa otomatik lead acilisi.
