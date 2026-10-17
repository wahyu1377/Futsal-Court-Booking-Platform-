# Arena Sport - Futsal Court Booking Platform

**Arena Sport** is a web-based digital platform designed to simplify the search, booking, and payment of futsal courts in real-time. The platform integrates real-time court schedule availability with digital payment systems, with an MVP that covers the core booking flow: Search → Select → Reserve → Pay → Confirm → Check In.

_Note: This repository is in the early development stage as part of Vinix Internship Task Module 7 - Web UI/UX Developer Division._

---

## Product Vision

> _"Make every court booking transparent, secure, and predictable—from the first search to venue check-in."_

Provide a **simple, reliable, and transparent** court booking experience without relying on phone calls or physical venue visits.

---

## Key Features

| Feature                 | Description                                                   |
| ----------------------- | ------------------------------------------------------------- |
| **Court Search**        | Search courts by location, date, time, and price             |
| **Real-Time Availability** | View court schedules live with an interactive calendar    |
| **Slot Locking**        | Slots are temporarily locked for 5 minutes during checkout    |
| **Digital Payment**     | QRIS & Virtual Account Bank Transfer                         |
| **Split Bill**          | Automatically divide booking costs among team members        |
| **Digital Ticket**      | Booking confirmation with QR Code                             |
| **Booking Dashboard**   | Manage upcoming, completed, and cancelled bookings           |

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

| Member Name                | Division            | Role                                           |
| :------------------------- | :------------------ | :--------------------------------------------- |
| **Aditya Latief Aji**       | Web UI/UX Developer | Homepage - Login - Register - PRD              |
| **Shayla Aziza**           | Web UI/UX Developer | Readme.md - PDF                               |
| **Wahyu Kurnia Pratama**   | Web UI/UX Developer | Schedule Details - Checkout                    |
| **Rifa Athallah Okky Putra** | Web UI/UX Developer | Account - Ticket                               |

---

## Tech Stack

| Layer        | Technology                             |
| ------------ | -------------------------------------- |
| **Frontend** | HTML5, Vanilla JavaScript              |
| **Styling**  | Tailwind CSS, Bootstrap 5 (components)  |
| **Backend**  | PHP Native (v8+)                       |
| **Database** | MySQL with PDO PHP                    |
| **Payment**  | QRIS, Virtual Account                  |

> **Prohibited:** React, Vue, jQuery, Laravel, or other external frameworks.

---

## Project Structure

```
arena-sport/
├── frontend/
│   ├── assets/
│   │   ├── css/          # Tailwind & custom styles
│   │   ├── img/          # Images & logos
│   │   └── js/           # Vanilla JavaScript
│   ├── components/       # Modular components (header, footer, navbar)
│   ├── pages/
│   │   ├── user/         # User pages (booking, checkout, ticket, account)
│   │   └── venue/        # Venue owner pages
│   └── index.php         # Landing page
├── backend/
│   ├── api/              # REST API endpoints
│   ├── config/           # Database & configuration
│   ├── controllers/     # Business logic
│   └── models/          # Data models
└── README.md
```

---

## Reference Links

- **Figma Design (Module 5):** [Arena Sport Figma](https://www.figma.com/design/bV3eySFtsCkgOdXU5yXN2O/BOOKING-LAPANGAN-FUTSAL?node-id=6-397&t=7YifXArREPyi3O4i-1)
- **PRD Document:** `Product Requirement Document Platform Marketplace Futsal.pdf`

---

## Development Status

| Module                                 | Status         |
| ------------------------------------- | -------------- |
| Module 4: Court Booking & Digital Payment | Completed    |
| Module 5: UI/UX Design                | Completed      |
| Module 6: Frontend Development         | In Progress    |
| Module 7: Backend Implementation      | Planned        |

---

## How to Run Locally

### Prerequisites
- PHP 8+ (with PDO extension)
- MySQL / MariaDB (with MySQLi extension)
- Git

### Step 1 — Clone repository
```bash
git clone https://github.com/wahyu1377/Futsal-Court-Booking-Platform.git
cd Futsal-Court-Booking-Platform
```

### Step 2 — Setup database
#### Option A — phpMyAdmin (easist)
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click **Databases** → Create database name: **arena_sport**
3. Click **Import** tab → Choose file **`arena_sport.sql`** → scroll to bottom → Click **Go**

#### Option B — MySQL CLI
```bash
mysql -u root -p
CREATE DATABASE arena_sport;
EXIT
mysql -u root -p arena_sport < arena_sport.sql
```

### Step 3 — Start PHP server
```bash
# From the project root folder
php -S localhost:8000
```

### Step 4 — Open browser
```
http://localhost:8000
```

---

## Key Performance Indicators (KPIs)

- **Booking Conversion Rate** - Percentage of users who complete a booking
- **Split Bill Adoption Rate** - Usage of the bill-splitting feature
- **Repeat Booking Rate** - Captains who book again within 30 days

---

## Security

- All traffic uses HTTPS/TLS
- Authentication and role-based authorization
- Payment gateway compliant
- QR Code does not expose sensitive data

---

_Developed as part of Vinix Internship Program - Web UI/UX Developer Division_
