# Arena Sport - Futsal Court Booking Platform

**Arena Sport** is a web-based digital platform designed to simplify the search, booking, and payment of futsal courts in real time. The platform combines live court schedule availability with a digital payment gateway, covering the core booking flow: **Search -> Select -> Reserve -> Pay -> Confirm -> Manage**.

_Note: This repository is in the early development stage as part of the Vinix Internship Task Module 7 - Web UI/UX Developer Division._

---

## Product Vision

> _"Make every court booking transparent, secure, and predictable - from the first search to venue check-in."_

Provide a **simple, reliable, and transparent** court booking experience without relying on phone calls or physical venue visits.

---

## Features That Are Currently Working

Every item below is implemented in this repository and can be exercised locally or on the deployed instance.

### 1. Authentication & Accounts

| Feature | Description |
| ------- | ----------- |
| **Register** | Create a **user** account (book courts) or an **owner** account (rent out courts) from the same form. |
| **Login** | Sign in with email **or** phone number. The account is locked for 15 minutes after 5 failed attempts. |
| **Sign in with Google** | Google Identity Services (GIS) button. The Google ID token is verified on the server before the session is created, and the account is registered automatically on first sign-in. |
| **Forgot password** | A reset link is e-mailed to the account. The endpoint always answers the same message, so it cannot be used to find out which addresses are registered. |
| **Reset password** | The token is single-use, expires after 60 minutes, and is stored only as a SHA-256 hash. A Google-only account (no password) is rejected with a clear message. |
| **Profile management** | Edit name, email and phone number, upload or delete a profile photo (avatar), and change the password. Available for both users and owners. |
| **Account settings** | Notification preferences (booking alerts, daily e-mail summary, WhatsApp) stored server-side, so they follow the account across devices. |
| **Help center** | FAQ accordion plus admin contact details, for both user and owner accounts. |

### 2. Finding a Court

| Feature | Description |
| ------- | ----------- |
| **Home / discovery** | Landing page with a quick search form and a list of courts. |
| **Search & filters** | Free-text search over name, sport type, district and city, plus filters for price range, minimum rating, facilities, "available today", and sorting by rating, cheapest, most expensive, or most reviewed. |
| **Favorites** | Save courts to a personal shortlist and open the "Favorites" page; the newest saved court is shown first. |
| **Court detail** | Photos, facilities, description, opening hours, session duration, and average rating. |
| **Live schedule** | The slot grid of a court for a chosen date, with free / booked / locked slots and the price of each slot. Bookable dates are limited to the next 7 days. |
| **Prime-time pricing** | Slots between 15:00 and 21:00 are automatically charged with a 1.2x multiplier; the price shown and the price charged always come from the same rule file (`backend/core/harga.php`). |

### 3. Booking & Payment

| Feature | Description |
| ------- | ----------- |
| **Checkout** | Pick one or more slots, add extras (training bibs, referee service, futsal balls), and choose to pay in full online or pay a down payment (DP) and settle the rest at the venue counter. Prices are recomputed on the server. |
| **Slot locking** | An unpaid booking holds its slots for 15 minutes, then expires automatically and releases the slots. A database constraint makes it impossible for two people to hold the same slot. |
| **Digital payment (DOKU)** | Online payment through the DOKU Checkout gateway (QRIS, Virtual Account, e-wallets). The checkout URL is created server-side and stored with the booking. |
| **Payment confirmation** | DOKU calls the notification webhook, which is verified with an HMAC-SHA256 signature and is idempotent. On localhost, where DOKU cannot reach the app, the user can press "I have paid, check status" and the app queries the DOKU status API instead. |
| **Booking management** | Booking list split into "Active" and "History", booking detail with a booking code (`BKG-XXXXXX`), and free cancellation while the booking is still waiting for payment. |

### 4. Venue Owner Dashboard

| Feature | Description |
| ------- | ----------- |
| **Dashboard statistics** | Total courts, active courts, average hourly rate, bookings today, bookings waiting for payment, and confirmed revenue for the current month. |
| **Incoming orders** | Read-only list of bookings for the owner's own courts, with a filter per court and separate "active" / "history" tabs. |
| **Court CRUD** | Create, update and delete courts: name, sport type, district, city, hourly price, facilities, opening hours, session duration, availability flag and photos. Every query is scoped with `owner_id`, so one owner can never touch another owner's court. |
| **Photo upload** | Court photos are validated with `getimagesize()` (max 3 MB, JPEG/PNG/WebP), renamed with a random unique filename, and stored in a Git-ignored folder. |
| **Owner account pages** | Profile, avatar, password change, notification settings, and help center, with a dedicated bottom navigation for owner pages. |

### 5. Platform & Security

| Feature | Description |
| ------- | ----------- |
| **Two databases, one codebase** | The same SQL runs on PostgreSQL (Supabase) and MySQL / MariaDB. No `ILIKE`, no `NOW() + INTERVAL`, no `RETURNING id`. |
| **Least-privilege access** | The app connects with a dedicated non-superuser role (`arena_app`) and never uses the `postgres` role. Row Level Security is enabled on every table. |
| **Password hashing** | Passwords are stored with `password_hash()` and verified with `password_verify()`; the plain password is never stored or logged. |
| **Server-side validation** | Every request body is validated again on the server. The client-side form is only a convenience layer. |
| **Role-based authorization** | `requireLogin('owner')` guards every owner page and owner endpoint; role comes from the table the account was found in (`users` or `owners`). |
| **Responsive, mobile-first UI** | PHP templates with Tailwind utilities, Bootstrap 5 only for interactive components (modal, accordion), and Bootstrap Icons. A bottom navigation for both user and owner pages. |

---

## Planned Features (not implemented yet)

These are part of the product concept but are **not** available in the current code base:

- Split bill / pay-together between team members
- QR-code digital ticket and venue check-in scanning
- Customer reviews (the rating columns exist, but no customer can submit a review yet)
- Waiting list for fully booked slots
- Saved payment methods per account

---

## Target Persona

**Dane** - 20-year-old university student and futsal team captain.

- Regularly organises futsal practice with university friends
- Digitally active, prefers online services
- Needs a fast, transparent and reliable booking system

---

## Main Booking Flow

```
1. Sign in (email / phone / Google)
2. Search for courts (name, district, city, price, facilities)
3. Filter and compare venue information
4. Open a court and pick an available slot
5. The slot is held for 15 minutes
6. Review the booking summary (slots + extras + DP option)
7. Pay online through DOKU (QRIS, Virtual Account, e-wallet)
8. Payment is verified -> booking status becomes "Confirmed"
9. Open the booking ticket (booking code BKG-XXXXXX)
10. Show the booking code at the venue counter
```

---

## Development Team (Group 23)

| Member Name                  | Division            | Role                                          |
| :--------------------------- | :------------------ | :-------------------------------------------- |
| **Aditya Latief Aji**        | Web UI/UX Developer | Homepage - Login - Register - PRD             |
| **Shayla Aziza**             | Web UI/UX Developer | Readme.md - PDF                               |
| **Wahyu Kurnia Pratama**     | Web UI/UX Developer | Schedule Details - Checkout                  |
| **Rifa Athallah Okky Putra** | Web UI/UX Developer | Account - Ticket                              |

---

## Tech Stack

| Layer        | Technology                                                             |
| ------------ | ---------------------------------------------------------------------- |
| **Frontend** | PHP (templating), HTML5, Vanilla JavaScript                            |
| **Styling**  | Tailwind CSS (utility classes), Bootstrap 5 (interactive components only) |
| **Backend**  | PHP Native (v8+)                                                      |
| **Database** | MySQL / MariaDB **or** PostgreSQL (Supabase), accessed through PDO      |
| **Payment**  | DOKU Checkout (QRIS, Virtual Account, e-wallet)                         |
| **Auth**     | Google Identity Services (GIS) for "Sign in with Google"                |
| **Mail**     | Built-in SMTP client (`backend/core/mailer.php`), no external library  |

> **Prohibited:** React, Vue, jQuery, Laravel, or any other external framework.

### Database Layer

The database connection is **environment driven** - no credentials are hardcoded in PHP.

- `backend/config/env.php` - a small `.env` parser (no Composer, no external dependency). It looks for the `.env` file in the repository root first, then in `backend/config/`.
- `backend/config/database.php` - reads the configuration, builds the DSN for the active driver (`mysql` or `pgsql`), opens a PDO connection, and aligns the session time zone to `Asia/Jakarta`.
- `backend/config/database.local.php` - optional per-machine override, ignored by Git (useful when one developer needs a local MySQL while the team uses Supabase).

Precedence: **real environment variables -> `.env` (shared by the team) -> `database.local.php` (local only)**.

> Setting a real environment variable always wins over `.env`, so the same code can be pointed at a different database without editing any file:
>
> ```bash
> DB_DRIVER=mysql DB_HOST=localhost DB_NAME=arena_sport DB_USER=root DB_PASS= php arena-sport/backend/smoke-test.php
> ```

### Database Schema

PostgreSQL (Supabase) is the **source of truth**. `arena_sport.sql` in the repository root is its MySQL/MariaDB counterpart - both produce the same tables, columns and behaviour, so the application does not need to treat the two databases differently.

| Table             | Purpose                                          | Key columns                                                                                       |
| ----------------- | ------------------------------------------------ | -------------------------------------------------------------------------------------------------- |
| `users`           | Player accounts                                  | `email`, `phone`, `password_hash`, `full_name`, `avatar`, `failed_attempts`, `locked_until`        |
| `owners`          | Venue owner accounts (kept separate from users)   | `email`, `phone`, `password_hash`, `full_name`, `avatar`, `failed_attempts`, `locked_until`       |
| `lapangan`        | Court catalogue                                  | `owner_id` -> `owners.id`, `nama`, `jenis`, `kecamatan`, `kota`, `harga_per_jam`, `rating`, `jumlah_ulasan`, `fasilitas`, `jam_buka`, `jam_tutup`, `durasi_sesi`, `tersedia_hari_ini`, `aktif` |
| `booking`         | Bookings (payment status, invoice, slots)        | `kode`, `user_id`, `lapangan_id`, `tanggal`, `status`, `total_harga`, `dp_dibayar`, `payment_url`, `expires_at` |
| `booking_slot`    | The slots held by a booking (concurrency guard)   | `booking_id`, `tanggal`, `jam_mulai`, `jam_selesai`, `aktif`                                      |
| `favorit`         | Saved courts per user                             | `user_id`, `lapangan_id`                                                                          |
| `password_reset`  | Password-reset tokens (SHA-256 hash only)        | `role`, `account_id`, `token_hash`, `expires_at`, `used_at`                                       |
| `user_settings`   | Notification preferences for users                | `user_id`, `notifikasi_pesanan`, `notifikasi_email`, `notifikasi_wa`                             |
| `owner_settings`  | Notification preferences for owners               | `owner_id`, `notifikasi_pesanan`, `notifikasi_email`, `notifikasi_wa`                            |

Deliberate type differences between the two databases (same behaviour either way):

| PostgreSQL          | MySQL / MariaDB      |
| ------------------- | -------------------- |
| `bigint` + identity | `int unsigned` + `auto_increment` |
| `boolean`           | `tinyint(1)`         |
| `timestamptz`       | `timestamp`          |
| `time`              | `time`               |

There is no `role` column: the role is derived from **which table** the account was found in (`users` -> user, `owners` -> owner).

The application SQL is written to run on **both** drivers - case-insensitive search uses `LOWER(col) LIKE LOWER(?)` instead of the PostgreSQL-only `ILIKE`, new IDs are read with `lastInsertId()` instead of `RETURNING id`, and account lockout timestamps are computed in PHP instead of `NOW() + INTERVAL`.

---

## Project Structure

```
arena-sport/
├── frontend/
│   ├── assets/
│   │   ├── css/          # Tailwind & custom styles (style.css, auth.css, akun.css, bottom-nav.css)
│   │   ├── img/          # Images & logos
│   │   └── js/           # Vanilla JavaScript (auth.js, auth-reset.js, profil.js, setelan.js)
│   ├── components/       # Reusable markup (head, header, navbar, footer, scripts, bottom nav)
│   ├── pages/
│   │   ├── auth/         # login, register, lupa-password (forgot password), reset-password, logout
│   │   ├── user/         # beranda (home), pencarian (search), detail, detail_jadwal (schedule),
│   │   │                 # checkout, bayar (payment), tiket (tickets), akun (account),
│   │   │                 # edit-profil, setelan (settings), favorit, bantuan (help)
│   │   └── owner/        # dashboard, tambah_lapangan (add court), akun (account),
│   │                     # edit-profil, setelan (settings), bantuan (help)
│   └── index.php         # Landing page
├── backend/
│   ├── api/
│   │   ├── auth/         # login, register, google, lupa-password, reset-password
│   │   ├── user/         # lapangan, jadwal, booking, favorit, profil, setelan
│   │   ├── owner/        # lapangan (CRUD + photo upload), pesanan, profil, setelan
│   │   └── payment/      # doku_notify.php (payment webhook)
│   ├── config/           # env.php, database.php, app.php, google.php, helpers.php, doku.*
│   ├── core/             # auth, helper, jadwal, harga, response, booking, doku,
│   │                     # mailer, password_reset, skema, url
│   ├── sql/              # Maintenance scripts (run via the Supabase SQL Editor)
│   ├── smoke-test.php    # Connection & schema check
│   └── index.php
├── .env.example          # Configuration template (copy it to .env)
└── README.md
```

---

## Configuration

### 1. Create your `.env`

```bash
cp .env.example .env
```

`.env` is already listed in `.gitignore` and must never be committed.

### 2. Fill in the values

```ini
# pgsql = PostgreSQL/Supabase   |   mysql = MySQL/MariaDB local
DB_DRIVER=pgsql

# Supabase (session pooler, port 5432)
DB_HOST=aws-0-ap-south-1.pooler.supabase.com
DB_PORT=5432
DB_NAME=postgres
DB_USER=arena_app.whmqodgxzjpbumtilrgr
DB_PASS=your-database-password
DB_SSLMODE=require
```

Switching back to a local MySQL (XAMPP) only takes a few commented lines:

```ini
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=arena_sport
DB_USER=root
DB_PASS=
```

### 3. Configure the mail sender (password reset)

```ini
APP_URL=http://localhost:8000/arena-sport   # base URL, used to build the reset link
MAIL_MODE=log                               # log = demo mode, smtp = real delivery
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=your@gmail.com
MAIL_PASS=your-app-password                 # use a Gmail App Password, not the account password
MAIL_FROM=arena.sport@example.com
MAIL_FROM_NAME=Arena Sport
```

In `log` mode the e-mail is not sent: it is written to `backend/logs/mail.log` and the reset link is shown on screen, which is convenient while testing on localhost.

### 4. Configure Google Sign-In

```ini
GOOGLE_CLIENT_ID=956268908707-xxxxxxxxxxxx.apps.googleusercontent.com
```

Leave it empty to use the built-in client ID in `backend/config/google.php`. The client ID is not a secret, but the origin still has to be registered in Google Cloud Console - see the *Google OAuth* section below.

### 5. Configure DOKU (payment gateway)

Online payment goes through **DOKU Checkout**. The configuration is split in two so the secret never lives in a PHP file:

**a) Credentials -> `.env`** (already gitignored, same place as the database password):

```ini
# DOKU Back Office -> Integration -> API Keys
DOKU_ENV=sandbox
DOKU_CLIENT_ID=BRN-XXXX-XXXXXXXXXXXXX
DOKU_SECRET_KEY=SK-XXXXXXXXXXXXXXXXXXXX
```

**b) Per-machine values -> `arena-sport/backend/config/doku.local.php`**

Copy the template first, then edit it:

```bash
cp arena-sport/backend/config/doku.example.php arena-sport/backend/config/doku.local.php
```

```php
return [
    'env'        => 'sandbox',                           // 'sandbox' or 'production'
    'app_url'    => 'http://localhost:8000/arena-sport', // base URL of the arena-sport folder
    'notify_url' => '',                                  // public HTTPS URL to doku_notify.php
];
```

| Key          | What it does |
| ------------ | ------------ |
| `app_url`    | Base address of the `arena-sport` folder. DOKU uses it to build the `callback_url` that returns the user to their ticket page after paying. Leave empty locally - the app derives it from the current request. |
| `notify_url` | Public HTTPS URL DOKU calls when a payment succeeds. Leave empty on localhost (DOKU cannot reach localhost) - the app then falls back to polling the status. Must match the URL registered in the DOKU Back Office exactly, otherwise the signature check fails. |

**Production URLs** (verified against the live domain):

```ini
DOKU_APP_URL=https://arena-sport.wasmer.app/arena-sport
DOKU_NOTIFY_URL=https://arena-sport.wasmer.app/arena-sport/backend/api/payment/doku_notify.php
```

The `/arena-sport` prefix is present because Wasmer currently serves the **repository root**. If the Wasmer root directory is changed to the `arena-sport` folder, drop the prefix from both URLs.

### Deploying to Wasmer (production)

`.env` is git-ignored, so it never reaches the deployed server. On Wasmer the configuration is supplied as **Environment Variables** in the app dashboard, which PHP exposes through `getenv()` - and `env()` in `backend/config/env.php` reads those first. Add the DOKU variables there:

| Variable          | Value |
| ----------------- | ----- |
| `DOKU_ENV`        | `sandbox` while testing, `production` for real payments |
| `DOKU_CLIENT_ID`  | From DOKU Back Office -> Integration -> API Keys |
| `DOKU_SECRET_KEY` | From DOKU Back Office -> Integration -> API Keys |
| `DOKU_APP_URL`    | `https://arena-sport.wasmer.app/arena-sport` |
| `DOKU_NOTIFY_URL` | `https://arena-sport.wasmer.app/arena-sport/backend/api/payment/doku_notify.php` |

Then register the same `DOKU_NOTIFY_URL` in **DOKU Back Office -> Settings -> Notification URL**, so DOKU posts the payment notification to the same address the app verifies against.

`doku.local.php` is already in `.gitignore`. `core/doku.php` merges the values in this order: built-in defaults -> `doku.local.php` -> `.env` (and real environment variables win over everything).

---

## How to Run Locally

### Prerequisites

- PHP 8+ (with the PDO extension, and `pdo_mysql` or `pdo_pgsql` depending on the driver you use)
- PHP extension **GD** (required for avatar upload on the account/profile pages - enable `extension=gd` in `php.ini`)
- MySQL / MariaDB (local) **or** a Supabase project
- Git

### Step 1 - Clone the repository

```bash
git clone https://github.com/wahyu1377/Futsal-Court-Booking-Platform.git
cd Futsal-Court-Booking-Platform
```

### Step 2 - Set up the database

#### Option A - MySQL local (phpMyAdmin)

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click **Databases** -> create a database named **arena_sport**
3. Click **Import** -> choose **`arena_sport.sql`** -> scroll to the bottom -> click **Go**
4. Set `DB_DRIVER=mysql` in your `.env` (see the *Configuration* section above)

The dump contains demo accounts:

| Role  | Email                 | Password   |
| ----- | --------------------- | ---------- |
| user  | `tes@gmail.com`       | `user123`  |
| owner | `owner@arenasport.id` | `owner123` |

#### Option B - Supabase (PostgreSQL)

1. Create a project at [supabase.com](https://supabase.com) and open **SQL Editor**.
2. Create the application role (least privilege - the app never uses the `postgres` role):
   ```sql
   CREATE ROLE arena_app LOGIN PASSWORD 'replace-with-a-strong-password';
   ```
3. Create the tables. The easiest way is to copy the structure from `arena_sport.sql` and adapt the types (see the *Database Schema* table above), or run a `pg_dump --schema-only` of a prepared project.
4. Grant access and enable Row Level Security:
   ```sql
   GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO arena_app;
   GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO arena_app;
   ALTER TABLE public.users     ENABLE ROW LEVEL SECURITY;
   ALTER TABLE public.lapangan ENABLE ROW LEVEL SECURITY;
   ALTER TABLE public.owners   ENABLE ROW LEVEL SECURITY;
   CREATE POLICY app_access_users    ON public.users     FOR ALL TO arena_app USING (true) WITH CHECK (true);
   CREATE POLICY app_access_lapangan ON public.lapangan FOR ALL TO arena_app USING (true) WITH CHECK (true);
   CREATE POLICY app_access_owners   ON public.owners   FOR ALL TO arena_app USING (true) WITH CHECK (true);
   REVOKE ALL ON ALL TABLES IN SCHEMA public FROM anon, authenticated;
   ```
5. Copy the connection values from **Settings -> Database** into your `.env` (use the *Session pooler* host and port `5432`).

> `arena_sport.sql` is the MySQL counterpart of this schema, so the two databases stay in sync. After switching drivers, always run the smoke test below.

### Step 3 - Start the PHP server

```bash
# From the project root folder
php -S localhost:8000
```

### Step 4 - Open the browser

```
http://localhost:8000
```

### Step 5 - Run the smoke test (recommended after any database change)

```bash
php arena-sport/backend/smoke-test.php
```

It checks the connection, verifies that the tables and all required columns exist, replays the critical queries (search, filter, lockout, insert), and reports PASS/FAIL per check. The exit code is `0` when everything passes.

### Database Maintenance Scripts

Scripts in `arena-sport/backend/sql/` need to be executed by the **table owner** (`postgres`), because the application role `arena_app` intentionally has no `DROP`/`ALTER` rights. Run them through **Supabase Dashboard -> SQL Editor -> New query -> paste -> Run**, then re-run the smoke test to confirm nothing broke.

| Script                         | Purpose |
| ------------------------------ | ------- |
| `003_housekeeping_index.sql`   | Drops the duplicate `lapangan_owner_id_idx` index and re-asserts the indexes the app needs. Idempotent, deletes no data. |
| `004_owner_akun.sql`           | Adds `owners.avatar` (owner profile photo) and the `owner_settings` table (owner notification preferences), including grants + RLS policy for `arena_app`. Optional: without it the owner account pages still work, but the two features stay disabled. |
| `005_password_reset.sql`       | Adds the `password_reset` table for the "Forgot Password" flow (PostgreSQL/Supabase), including grants + RLS policy for `arena_app`. Optional: without it the reset endpoints answer `503` with a clear message. |
| `005_password_reset.mysql.sql` | The same table for a local MySQL/MariaDB (XAMPP) - `AUTO_INCREMENT` + `DATETIME`, no RLS. Use this one when running the project on a local MySQL database. |
| `006_user_settings.sql`        | Adds the `user_settings` table (notification preferences for regular users, mirroring `owner_settings`), including grants + RLS policy for `arena_app`. Optional: without it the preferences are stored in the browser only. |
| `006_user_settings.mysql.sql`  | The same table for a local MySQL/MariaDB (XAMPP). |

---

## API Overview

All endpoints live under `backend/api/` and speak JSON.

| Endpoint                | Method             | Purpose |
| ----------------------- | ------------------ | ------- |
| `auth/register.php`     | POST               | Register as `user` or `owner` |
| `auth/login.php`        | POST               | Login with email/phone (lockout after 5 failed attempts) |
| `auth/google.php`       | POST               | Sign in with Google (the Google ID token is verified server-side) |
| `auth/lupa-password.php`| POST               | Request a password-reset link (always answers the same message, never reveals whether the account exists) |
| `auth/reset-password.php`| GET / POST         | Validate a reset token, then set the new password |
| `user/lapangan.php`     | GET                | List and filter the courts (`q`, `jenis`, `min_harga`, `max_harga`, `rating_min`, `fasilitas`, `tersedia`, `urut`, `favorit`) |
| `user/jadwal.php`       | GET                | Slot availability of a court (`id`, `tanggal`) - public |
| `user/booking.php`      | GET / POST         | Booking list, create a booking, get the DOKU payment URL, check the payment status, cancel |
| `user/favorit.php`      | POST               | Add or remove a court from the favorites |
| `user/profil.php`       | GET / POST         | User profile: read, update name/email/phone, upload or delete the avatar, change the password |
| `user/setelan.php`      | GET / POST         | User notification preferences (stored in `user_settings`) |
| `owner/lapangan.php`    | GET / POST / PUT / DELETE | Court CRUD scoped to the logged-in owner |
| `owner/lapangan.php`    | POST (multipart)   | Upload court photos |
| `owner/pesanan.php`     | GET                | Incoming bookings for the owner's courts (`tab=aktif|riwayat`, `lapangan_id`) |
| `owner/profil.php`      | GET / POST         | Owner profile: update name/email/phone, upload or delete the avatar, change the password |
| `owner/setelan.php`     | GET / POST         | Owner notification preferences (stored in `owner_settings`) |
| `payment/doku_notify.php` | POST             | DOKU payment webhook (signature-verified, idempotent) |

Court ownership is always enforced in the query itself (`WHERE id = ? AND owner_id = ?`), so one owner can never modify or delete another owner's court.

### DOKU payment flow

1. The user books a slot -> `user/booking.php` creates the booking and calls `dokuBuatSesi()` in `backend/core/doku.php`.
2. DOKU returns a checkout URL, which is stored in `booking.payment_url` and the user is redirected to it.
3. After payment, DOKU calls `payment/doku_notify.php` (HTTP Notification). The request is only accepted when its HMAC-SHA256 signature matches (`dokuVerifikasiNotifikasi()`).
4. If the notification cannot reach the app (for example while developing on localhost), the user presses "I have paid, check status" on `bayar.php`, which queries the DOKU Check Status API instead (`sinkronStatusDoku()`).

### Booking lifecycle

```
menunggu  --(payment received)-->  dibayar  --(playing time has passed)-->  selesai
menunggu  --(user cancels)------->  dibatalkan
menunggu  --(15 minutes elapsed)-->  kedaluwarsa   (the held slots are released)
```

Unpaid bookings hold their slots for 15 minutes (`HOLD_MENIT` in `backend/core/booking.php`). A database constraint rejects two active bookings with overlapping times, so two people pressing "pay" at the same moment can never both succeed.

### Password reset flow

1. `frontend/pages/auth/lupa-password.php` posts the email/phone to `auth/lupa-password.php`. The endpoint answers the **same** message whether or not the account exists, so it cannot be used to enumerate registered users. The only exception is a Google-only account (no `password_hash`), which gets a specific message.
2. A 32-byte random token is generated. Only its **SHA-256 hash** is stored in `password_reset` - the plain token exists only inside the e-mail, so a database leak does not allow a takeover.
3. The link `frontend/pages/auth/reset-password.php?token=...` is valid for 60 minutes and single-use. It is checked with `GET auth/reset-password.php` before the form is shown.
4. `POST auth/reset-password.php` writes the new `password_hash`, marks the token as used, deletes the account's other tokens, and clears `failed_attempts` / `locked_until` so a locked-out account can log in again.
5. Abuse protection: 60-second cooldown per account, max 5 requests/hour per account and max 10 requests/hour per IP.

### Mail configuration

`backend/core/mailer.php` sends the e-mail without any third-party library, controlled by `.env`:

| Variable                       | Meaning |
| ------------------------------ | ------- |
| `APP_URL`                      | Base URL of the `arena-sport` folder, used to build the reset link. Guessed from the request when empty. |
| `MAIL_MODE`                    | `log` (default, demo: the e-mail is written to `backend/logs/mail.log` and the link is shown on screen) or `smtp` (real delivery). |
| `MAIL_HOST` / `MAIL_PORT`      | SMTP server, e.g. `smtp.gmail.com` and `587`. |
| `MAIL_USER` / `MAIL_PASS`      | SMTP credentials - for Gmail use an **App Password**, not the real account password. |
| `MAIL_FROM` / `MAIL_FROM_NAME` | Sender shown in the e-mail. |

### Google OAuth ("Sign in with Google")

The button on `login.php` / `register.php` is rendered by **Google Identity Services** (GIS) inside an iframe served by `accounts.google.com`. Before Google renders it, the **origin of the page** is checked against the *Authorized JavaScript origins* of the OAuth 2.0 client ID. If the origin is not on the list, Google answers `403` and the button stays empty.

**Setup (Google Cloud Console -> APIs & Services -> Credentials):**

1. Create an **OAuth 2.0 Client ID** of type **Web application**.
2. Under **Authorized JavaScript origins**, add the origin of the app - *scheme + host + port, no path, no trailing slash*:
   - local: `http://localhost:8000` (and `http://127.0.0.1:8000` if you browse that way)
   - production: `https://arena-sport.wasmer.app`
3. Put the client ID in `.env` (`GOOGLE_CLIENT_ID=`). When empty, the fallback in `backend/config/google.php` is used.

> **The port is part of the origin.** `http://localhost` is **not** the same origin as `http://localhost:8000`, and `http://127.0.0.1:8000` is yet another one. Adding the wrong variant is the most common cause of a blank button.

**Symptoms and their meaning:**

| Symptom | Cause |
| ------- | ----- |
| `403` on `accounts.google.com/gsi/button` + `[GSI_LOGGER]: The given origin is not allowed for the given client ID` | The origin is missing (or misspelled) in *Authorized JavaScript origins*. |
| The button area is blank and nothing is logged | The `gsi/client` script was blocked (ad-blocker, offline, strict CSP) - `auth.js` shows a message for this case. |
| `400 invalid_grant` after clicking the button | The OAuth consent screen is in *Testing* mode and the Google account is not listed as a test user. |
| The token is reported as invalid | The client ID in `.env` does not match the one the token was issued for. |

**Checking the configuration:**

```bash
php arena-sport/backend/smoke-test.php
```

The smoke test prints the exact origin that must be registered and asks Google directly whether that origin is allowed (`HTTP 200` = OK, `HTTP 403` = not registered yet).

---

## Security

- **Least-privilege database access** - the application connects with a dedicated non-superuser role (`arena_app`) that has no `BYPASSRLS`, no `CREATEROLE`, and no `CREATEDB`. The `postgres` role is never used by the application.
- **Row Level Security** - enabled on every application table, with policies scoped to the application role. The `anon` and `authenticated` roles are revoked so the public API key cannot read the tables.
- **Credentials never in Git** - `.env` is git-ignored; only `.env.example` is committed. The DOKU `client_id`/`secret_key` live in `.env` too, so no secret is ever stored in a PHP file.
- **Password hashing** - passwords are stored as hashes (`password_hash` / `password_verify`), never in plain text.
- **Password reset without account enumeration** - reset tokens are single-use, expire after 60 minutes, are stored only as SHA-256 hashes, and the request endpoint always returns the same neutral message.
- **Login lockout** - after 5 failed attempts the account is locked for 15 minutes (`MAX_ATTEMPTS` / `LOCK_MINUTES` in `backend/config/database.php`).
- **Role-based authorization** - `requireLogin('owner')` guards every owner page and owner endpoint.
- **Server-side validation** - every request body is re-validated on the server; the client-side form is only a convenience layer.
- **File upload hardening** - uploaded photos are checked with `getimagesize()` (max 3 MB, JPEG/PNG/WebP only), given a random unique filename, and stored in `frontend/assets/img/lapangan/owner/` which is Git-ignored.
- **Slot double-booking protection** - a database constraint makes overlapping active bookings impossible, and the price is always recomputed on the server.

---

## Development Status

| Module                                  | Status      |
| --------------------------------------- | ----------- |
| Module 4: Court Booking & Digital Payment | Completed |
| Module 5: UI/UX Design                 | Completed   |
| Module 6: Frontend Development          | In Progress |
| Module 7: Backend Implementation       | In Progress |

---

## Key Performance Indicators (KPIs)

- **Booking Conversion Rate** - percentage of users who complete a booking
- **Repeat Booking Rate** - captains who book again within 30 days
- **Owner Utilization** - share of court slots that are booked

---

## Reference Links

- **Figma Design (Module 5):** [Arena Sport Figma](https://www.figma.com/design/bV3eySFtsCkgOdXU5yXN2O/BOOKING-LAPANGAN-FUTSAL?node-id=6-397&t=7YifXArREPyi3O4i-1)
- **PRD Document:** `Product Requirement Document Platform Marketplace Futsal.pdf`
- **Supabase Docs:** [PostgreSQL connection strings](https://supabase.com/docs/guides/database/connecting-to-postgres)

---

_Developed as part of the Vinix Internship Program - Web UI/UX Developer Division_
