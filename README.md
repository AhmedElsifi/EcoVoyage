# 🌿 EcoVoyage

EcoVoyage is a sustainable travel platform that connects eco‑conscious travelers with local guides, while regional auditors ensure all tours meet sustainability standards.  
Built with a custom PHP MVC architecture, MySQL, and Bootstrap 5 for a clean, responsive interface.

---

## ✨ Features

- **Role‑based access** – travelers, guides, auditors, and admins each have dedicated dashboards.
- **Browse & search eco‑tours** – filter by type, budget, and sustainability impact tags.
- **Detailed tour pages** – itineraries, pricing, guide profiles, and carbon‑footprint info.
- **Registration flow** – users select their role and provide role‑specific details (guides upload IDs, auditors submit CVs).
- **Authentication** – login with password hashing, automatic redirection to the correct dashboard.
- **Sustainability focus** – impact tags, carbon offset tracking, waste management, and local hiring indicators.
- **Fully responsive** – built with Bootstrap 5 and a custom eco‑friendly green theme.

---

## 🧱 Tech Stack

- **Backend:** PHP 8+ (custom MVC framework)
- **Database:** MySQL / MariaDB
- **Frontend:** Bootstrap 5, Bootstrap Icons, vanilla JavaScript
- **Server:** Apache with `.htaccess` (XAMPP / Laragon compatible)

---

## 📁 Project Structure
```
EcoVoyage/
├── app/
│ ├── controllers/ # Application controllers (Auth, Guest, Admin, etc.)
│ ├── core/ # Core classes (Router, Database, Controller, Validator, etc.)
│ ├── models/ # Database models (User, Tour, Guide, Location, etc.)
│ └── views/ # View files organized by role (guest, auth, admin, etc.)
├── config/
│ └── config.php # (optional) configuration file
├── public/
│ ├── .htaccess # URL rewriting rules
│ ├── index.php # Front controller & autoloader
│ └── uploads/ # User‑uploaded files (IDs, CVs, tour images)
├── ecovoyage.sql # Database schema and sample data
└── README.md
```

## ⚙️ Setup Instructions

### 1. Clone the repository
```bash
git clone https://github.com/AhmedElsifi/EcoVoyage.git
cd EcoVoyage
```

## 2. Set up the database
Create a database named ecovoyage in your `MySQL` server.

Import the provided SQL file:
```mysql
mysql -u root -p ecovoyage < ecovoyage.sql
```
Update the database credentials in `app/core/Database.php` if needed.

### 3. Configure the project
- Open `public/index.php` and set `BASE_URL` to match your local setup:
  ```php
  define('BASE_URL', 'http://localhost/EcoVoyage/public/');
  ```
- Ensure the `public/` folder is accessible (set the document root to `public/` or use the `/public/` URL pattern).

### 4. Start your local server
- Place the project inside your XAMPP/Laragon `htdocs` folder.
- Visit: `http://localhost/EcoVoyage/public/`

---

## 👥 User Roles

| Role | Default Dashboard | Key Abilities |
|------|-------------------|---------------|
| **Traveler** | `/traveler/dashboard` | Browse tours, book experiences, leave reviews |
| **Guide** | `/guide/dashboard` | Create and manage eco‑tours, upload ID, track earnings |
| **Regional Auditor** | `/auditor/dashboard` | Review guides/tours in assigned region (requires admin approval) |
| **Admin** | `/admin/dashboard` | Manage users, verify guides/auditors, platform settings |
| **Guest** | `/guest/index` | View public tours, register or login |

---

## 🔒 Security

- Password hashing with `password_hash()` and `password_verify()`.
- Server‑side validation (custom `Validator` class) with XSS prevention (`noHtml()`).
- Role‑based access control in each controller constructor.
- Prepared statements for all database queries (PDO).

---

## 📝 License

This project is for educational/demonstration purposes. Feel free to adapt and extend it.

---

Built with ❤️ for the planet 🌍
