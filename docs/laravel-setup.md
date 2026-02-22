# Laravel Setup (cPanel/SSH)

Bu repoda Laravel framework dosyaları henüz yok; kurulum için composer ile iskelet çekilmesi gerekiyor.

## 1) Laravel iskeletini oluştur

```bash
composer create-project laravel/laravel .
```

## 2) Bu repodaki iş dosyalarını geri kopyala

- `db/schema.sql`
- `docs/*`
- `.env.example` (birleştir)

## 3) Ortam kurulumları

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

## 4) İlk sprint modülleri

- Auth (Breeze)
- Companies import endpoint
- Leads CRUD + status flow
- 10 gün follow-up scheduler

## 5) cPanel cron

```bash
* * * * * php /home/<user>/<app-root>/artisan schedule:run >> /dev/null 2>&1
```
