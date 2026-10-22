# Arena Sport - Futsal Court Booking Platform

**Arena Sport** is a web-based digital platform designed to simplify the search, booking, and payment of futsal courts in real-time. The platform integrates real-time court schedule availability with digital payment systems, with an MVP that covers the core booking flow: Search → Select → Reserve → Pay → Confirm → Check In.

_Note: This repository is in the early development stage as part of Vinix Internship Task Module 7 - Web UI/UX Developer Division._

---

## Product Vision

> _"Make every court booking transparent, secure, and predictable—from the first search to venue check-in."_

Provide a **simple, reliable, and transparent** court booking experience without relying on phone calls or physical venue visits.

---

## Key Features

| Feature                     | Description                                                                |
| --------------------------- | -------------------------------------------------------------------------- |
| **Court Search**            | Search courts by location, date, time, and price                          |
| **Real-Time Availability**  | View court schedules live with an interactive calendar                     |
| **Slot Locking**            | Slots are temporarily locked for 5 minutes during checkout                 |
| **Digital Payment**         | QRIS & Virtual Account Bank Transfer                                      |
| **Split Bill**              | Automatically divide booking costs among team members                       |
| **Digital Ticket**          | Booking confirmation with QR Code                                          |
| **Booking Dashboard**       | Manage upcoming, completed, and cancelled bookings                        |
| **Venue Owner Dashboard**   | Venue owners manage their own courts: create, update, delete, upload photos |
| **Dynamic Court Rating**    | Rating and review count are shown only when real customer ratings exist    |
| **Prime-Time Pricing**      | Slot price is adjusted automatically for peak hours                        |

---

## Target Persona

**Dane** - 20-year-old University Student & Futsal Team Captain

- Regularly organizes futsal practice with university friends
- Digitally active and prefers online services
- Needs a fast, transparent, and reliable booking system

---

## Main Booking Flow

```
1. Login to the platform
2. Search for courts (location, date, time)
3. Filter and compare venue information
4. Select court and available slot
5. Slot is temporarily locked (5 minutes)
6. Review booking summary
7. Choose payment method (Individual / Split Bill)
8. Complete payment
9. Verification → Booking status updated
10. Receive digital ticket + QR Code
11. Show QR Code at venue check-in
```

---

## Development Team (Group 23)

| Member Name                   | Division            | Role                                           |
| :---------------------------- | :------------------ | :--------------------------------------------- |
| **Aditya Latief Aji**         | Web UI/UX Developer | Homepage - Login - Register - PRD              |
| **Shayla Aziza**              | Web UI/UX Developer | Readme.md - PDF                                |
| **Wahyu Kurnia Pratama**      | Web UI/UX Developer | Schedule Details - Checkout                    |
| **Rifa Athallah Okky Putra**  | Web UI/UX Developer | Account - Ticket                               |

---

## Tech Stack

| Layer         | Technology                                                              |
| ------------- | ----------------------------------------------------------------------- |
| **Frontend**  | PHP (templating), HTML5, Vanilla JavaScript                              |
| **Styling**   | Tailwind CSS (utility classes), Bootstrap 5 (interactive components only)  |
| **Backend**   | PHP Native (v8+)                                                        |
| **Database**  | MySQL / MariaDB **or** PostgreSQL (Supabase), accessed through PDO       |
| **Payment**   | QRIS, Virtual Account                                                   |

> **Prohibited:** React, Vue, jQuery, Laravel, or other external frameworks.

### Database Layer

The database connection is **environment driven** — no credentials are hardcoded in PHP.

- `backend/config/env.php` — a small `.env` parser (no Composer / no external dependency). It looks for the `.env` file in the repository root first, then in `backend/config/`.
- `backend/config/database.php` — reads the configuration, builds the DSN for the active driver (`mysql` or `pgsql`), opens a PDO connection, and aligns the session time zone to `Asia/Jakarta`.
- `backend/config/database.local.php` — optional per-machine override, ignored by Git (useful when one developer needs to point to a local MySQL while the team uses Supabase).

Precedence: **real environment variables → `.env` (shared by the team) → `database.local.php` (local only)**.

> Setting a real environment variable always wins over `.env`, so the same code can be pointed at a different database without editing any file:
> ```bash
> DB_DRIVER=mysql DB_HOST=localhost DB_NAME=arena_sport DB_USER=root DB_PASS= php arena-sport/backend/smoke-test.php
> ```

### Database Schema

PostgreSQL (Supabase) is the **source of truth**. `arena_sport.sql` in the repository root is its MySQL/MariaDB counterpart — both produce the same tables, columns, and behaviour, so the application does not need to treat the two databases differently.

| Table       | Purpose                                        | Key columns                                                                                     |
| ----------- | ---------------------------------------------- | ----------------------------------------------------------------------------------------------- |
| `users`     | Player accounts                                | `email`, `phone`, `password_hash`, `failed_attempts`, `locked_until`                            |
| `owners`    | Venue owner accounts (kept separate from users) | `email`, `phone`, `password_hash`, `failed_attempts`, `locked_until`                           |
| `lapangan`  | Court catalogue                                | `owner_id` → `owners.id`, `nama`, `jenis`, `harga_per_jam`, `rating`, `fasilitas`, `jam_buka`, `jam_tutup`, `durasi_sesi`, `tersedia_hari_ini`, `aktif` |

Deliberate type differences between the two databases (same behaviour either way):

| PostgreSQL          | MySQL / MariaDB      |
| ------------------- | -------------------- |
| `bigint` + identity | `int unsigned` + `auto_increment` |
| `boolean`           | `tinyint(1)`         |
| `timestamptz`       | `timestamp`          |
| `time`              | `time`               |

There is no `role` column: the role is derived from **which table** the account was found in (`users` → user, `owners` → owner).

The application SQL is written to run on **both** drivers — case-insensitive search uses `LOWER(col) LIKE LOWER(?)` instead of PostgreSQL-only `ILIKE`, new IDs are read with `lastInsertId()` instead of `RETURNING id`, and account lockout timestamps are computed in PHP instead of `NOW() + INTERVAL`.

---

## Project Structure

```
arena-sport/
├── frontend/
│   ├── assets/
│   │   ├── css/          # Tailwind & custom styles (app.css, owner.css)
│   │   ├── img/          # Images & logos
│   │   └── js/           # Vanilla JavaScript (beranda.js, owner-lapangan.js)
│   ├── components/       # Modular components (header, footer, navbar, scripts)
│   ├── pages/
│   │   ├── auth/         # Login & register
│   │   ├── user/         # User pages (beranda, pencarian, detail, checkout, tiket, akun)
│   │   └── owner/        # Venue owner pages (dashboard, tambah_lapangan)
│   └── index.php         # Landing page
├── backend/
│   ├── api/
│   │   ├── auth/         # login.php, register.php
│   │   ├── user/         # lapangan, booking, jadwal, profil
│   │   └── owner/        # lapangan (CRUD + photo upload)
│   ├── config/           # env.php, database.php, database.local.php
│   ├── core/             # auth, helper, jadwal, harga, response
│   ├── sql/              # Maintenance scripts (run via Supabase SQL Editor)
│   └── index.php
├── .env.example          # Template configuration (copy it to .env)
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

---

## How to Run Locally

### Prerequisites

- PHP 8+ (with the PDO extension, and `pdo_mysql` or `pdo_pgsql` depending on the driver you use)
- MySQL / MariaDB (local) **or** a Supabase project
- Git

### Step 1 — Clone repository

```bash
git clone https://github.com/wahyu1377/Futsal-Court-Booking-Platform.git
cd Futsal-Court-Booking-Platform
```

### Step 2 — Setup the database

#### Option A — MySQL local (phpMyAdmin)

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click **Databases** → create a database named **arena_sport**
3. Click **Import** → choose **`arena_sport.sql`** → scroll to the bottom → click **Go**
4. Set `DB_DRIVER=mysql` in your `.env` (see the *Configuration* section above)

The dump contains three demo accounts:

| Role  | Email                  | Password   |
| ----- | ---------------------- | ---------- |
| user  | `tes@gmail.com`        | `user123`  |
| owner | `owner@arenasport.id`  | `owner123` |

#### Option B — Supabase (PostgreSQL)

1. Create a project at [supabase.com](https://supabase.com) and open **SQL Editor**.
2. Create the application role (least privilege — the app never uses the `postgres` role):
   ```sql
   CREATE ROLE arena_app LOGIN PASSWORD 'ganti-dengan-password-kuat';
   ```
3. Create the `users`, `owners`, and `lapangan` tables. The easiest way is to copy the structure from `arena_sport.sql` and adapt the types (see the *Database Schema* table above), or run a `pg_dump --schema-only` of a prepared project.
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
5. Copy the connection values from **Settings → Database** into your `.env` (use the *Session pooler* host and port `5432`).

> `arena_sport.sql` is the MySQL counterpart of this schema, so the two databases stay in sync. After switching drivers, always run the smoke test below.

### Step 3 — Start PHP server

```bash
# From the project root folder
php -S localhost:8000
```

### Step 4 — Open browser

```
http://localhost:8000
```

### Step 5 — Run the smoke test (recommended after any database change)

```bash
php arena-sport/backend/smoke-test.php
```

It checks the connection, verifies that the three tables and all required columns exist, replays the critical queries (search, filter, lockout, insert), and reports PASS/FAIL per check. Exit code is `0` when everything passes.

### Database Maintenance Scripts

Scripts in `arena-sport/backend/sql/` need to be executed by the **table owner** (`postgres`), because the application role `arena_app` intentionally has no `DROP`/`ALTER` rights. Run them through **Supabase Dashboard → SQL Editor → New query → paste → Run**, then re-run the smoke test to confirm nothing broke.

| Script                             | Purpose                                                            |
| ---------------------------------- | ------------------------------------------------------------------ |
| `003_housekeeping_index.sql`       | Drops the duplicate `lapangan_owner_id_idx` index and re-asserts the indexes the app needs. Idempotent, deletes no data. |

---

## API Overview

All endpoints live under `backend/api/` and speak JSON.

| Endpoint                              | Method                | Purpose                                       |
| ------------------------------------- | --------------------- | --------------------------------------------- |
| `auth/register.php`                   | POST                  | Register as `user` or `owner`                 |
| `auth/login.php`                      | POST                  | Login (with lockout after 5 failed attempts)  |
| `user/lapangan.php`                   | GET                   | List courts available for booking              |
| `user/jadwal.php`                     | GET                   | Schedule & slot availability                  |
| `user/booking.php`                    | GET / POST            | Booking list & creation                       |
| `user/profil.php`                     | GET / PUT             | User profile                                  |
| `owner/lapangan.php`                  | GET / POST / PUT / DELETE | Court CRUD scoped to the logged-in owner   |
| `owner/lapangan.php`                  | POST (multipart)      | Upload court photos                           |

Court ownership is always enforced in the query itself (`WHERE id = ? AND owner_id = ?`), so one owner can never modify or delete another owner's court.

---

## Security

- **Least-privilege database access** — the application connects with a dedicated non-superuser role (`arena_app`) that has no `BYPASSRLS`, no `CREATEROLE`, and no `CREATEDB`. The `postgres` role is never used by the application.
- **Row Level Security** — enabled on every application table, with policies scoped to the application role. The `anon` and `authenticated` roles are revoked so the public API key cannot read the tables.
- **Credentials never in Git** — `.env` is git-ignored; only `.env.example` is committed.
- **Password hashing** — passwords are stored as hashes (`password_hash` / `password_verify`), never in plain text.
- **Login lockout** — after 5 failed attempts the account is locked for 15 minutes (`MAX_ATTEMPTS` / `LOCK_MINUTES` in `backend/config/database.php`).
- **Role-based authorization** — `requireLogin('owner')` guards every owner page and owner endpoint.
- **Server-side validation** — every request body is re-validated on the server; the client-side form is only a convenience layer.
- **File upload hardening** — uploaded photos are checked with `getimagesize()` (max 3 MB, JPEG/PNG/WebP only), given a random unique filename, and stored in `frontend/assets/img/lapangan/owner/` which is Git-ignored.

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

- **Booking Conversion Rate** - Percentage of users who complete a booking
- **Split Bill Adoption Rate** - Usage of the bill-splitting feature
- **Repeat Booking Rate** - Captains who book again within 30 days

---

## Reference Links

- **Figma Design (Module 5):** [Arena Sport Figma](https://www.figma.com/design/bV3eySFtsCkgOdXU5yXN2O/BOOKING-LAPANGAN-FUTSAL?node-id=6-397&t=7YifXArREPyi3O4i-1)
- **PRD Document:** `Product Requirement Document Platform Marketplace Futsal.pdf`
- **Supabase Docs:** [PostgreSQL connection strings](https://supabase.com/docs/guides/database/connecting-to-postgres)

---

_Developed as part of Vinix Internship Program - Web UI/UX Developer Division_
