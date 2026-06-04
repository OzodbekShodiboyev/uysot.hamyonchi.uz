# UySotish Pro — O'rnatish yo'riqnomasi
## Laravel 10 · PHP 8.1 · beyondcode/laravel-websockets

---

## Talablar

| Komponent | Versiya |
|-----------|---------|
| PHP | 8.1+ |
| Laravel | 10.x |
| MySQL | 8.0+ |
| Composer | 2.x |

---

## 1-qadam: O'rnatish

```bash
# .env ni tayorlash
cp .env.example .env

# Kerakli maydonlarni to'ldiring:
# DB_DATABASE, DB_USERNAME, DB_PASSWORD
# PUSHER_APP_KEY, PUSHER_APP_SECRET

# Paketlarni o'rnatish
composer install --no-dev --optimize-autoloader

# App kaliti generatsiyasi
php artisan key:generate

# Ma'lumotlar bazasini yaratish
php artisan migrate

# Demo ma'lumotlar (ixtiyoriy)
php artisan db:seed
```

---

## 2-qadam: Cache va storage

```bash
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 3-qadam: Real-time (beyondcode/laravel-websockets)

```bash
# WebSocket serverini ishga tushirish
php artisan websockets:serve

# Production uchun Supervisor:
# [program:websockets]
# command = php /var/www/uy-sotish/artisan websockets:serve
# autostart = true
# autorestart = true
```

---

## 4-qadam: Queue Worker

```bash
# Queue worker
php artisan queue:work --queue=default --sleep=3 --tries=3

# Production Supervisor:
# [program:queue-worker]
# command = php /var/www/uy-sotish/artisan queue:work --sleep=3 --tries=3
# autostart = true
# autorestart = true
# numprocs = 2
```

---

## 5-qadam: Cron job (Scheduler)

```bash
# crontab -e
* * * * * cd /var/www/uy-sotish && php artisan schedule:run >> /dev/null 2>&1
```

**Scheduler nima qiladi?**
- Har 5 daqiqada: muddati o'tgan bronlarni avtomatik bo'shatish
- Har kuni 00:05: muddati o'tgan to'lovlarni belgilash

---

## Demo kirish ma'lumotlari

| Rol | Email | Parol |
|-----|-------|-------|
| Admin | admin@uysotish.uz | Admin1234! |
| Menejer 1 | sardor@uysotish.uz | Manager123! |
| Menejer 2 | dilnoza@uysotish.uz | Manager123! |
| Hisobchi | zafar@uysotish.uz | Accountant123! |

---

## Shartnoma raqami formati

```
{YIL}/{BLOK_KODI}-{TARTIB_4_RAQAM}

Misollar:
  2024/B1-0001  →  2024-yil, 1-blok, 1-shartnoma
  2024/B2-0015  →  2024-yil, 2-blok, 15-shartnoma
  2025/B1-0001  →  Yangi yildan qayta boshlanadi
```

**Mexanizm:** `contract_sequences` jadvalida `block_id + year` bo'yicha
`lockForUpdate()` bilan atomik increment — bir vaqtda ikki menejer
bir raqamni ola olmaydi.

---

## Kvitansiya raqami formati

```
KV-{YIL}-{TARTIB_6_RAQAM}

Misollar:
  KV-2024-000001
  KV-2025-000001  →  Yangi yildan qayta boshlanadi
```

---

## To'lov turlari (3 xil)

| Tur | payment_type | initial_payment | installment_months |
|-----|-------------|-----------------|---------------------|
| 100% naqd | `full` | 0 | 0 |
| Boshlang'ich + bo'lib | `installment` | > 0 | > 0 |
| Boshlang'ichsiz bo'lib | `installment` | 0 | > 0 |

---

## Loyiha arxitekturasi

```
app/
├── Models/
│   ├── User.php                — Foydalanuvchilar (4 rol)
│   ├── Block.php               — Bloklar
│   ├── Apartment.php           — Xonadonlar
│   ├── Contract.php            — Shartnomalar + raqam generatsiyasi
│   └── OtherModels.php         — Client, Owner, Payment, PaymentSchedule,
│                                  ActivityLog, ContractSequence
├── Services/
│   └── ContractService.php     — Barcha biznes logika
│       ├── create()            — lockForUpdate bilan shartnoma yaratish
│       ├── activate()          — Draft → Active
│       ├── receivePayment()    — To'lov + kvitansiya
│       ├── reserve()           — Vaqtinchalik band qilish
│       ├── release()           — Bandlikni bekor qilish
│       └── cancel()            — Shartnomani bekor qilish
├── Events/
│   └── ApartmentStatusChanged  — Pusher orqali real-time broadcast
├── Jobs/
│   ├── ReleaseExpiredReservations — Har 5 daqiqada
│   └── MarkOverduePayments        — Har kuni
└── Exports/
    └── ContractsExport.php     — Excel eksport

resources/views/
├── layouts/app.blade.php       — Asosiy layout (sidebar + Pusher Echo)
├── auth/login.blade.php        — Kirish sahifasi
├── dashboard/index.blade.php   — Bosh sahifa
├── apartments/block.blade.php  — Qavatma-qavat ko'rinish
├── contracts/
│   ├── index.blade.php         — Shartnomalar ro'yxati
│   ├── create.blade.php        — Yangi shartnoma (3 to'lov turi)
│   ├── show.blade.php          — Shartnoma tafsiloti
│   └── pdf.blade.php           — DomPDF chop etish shabloni
├── clients/{index,show}        — Mijozlar
├── payments/{index,overdue}    — To'lovlar
└── reports/index.blade.php     — Hisobotlar va grafik
```
