# 🗳️ VoteApp — Smart Online Voting System
## 📋 Table of Contents

- [About the Project](#about-the-project)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Database Schema](#database-schema)
- [Setup Instructions](#setup-instructions)
- [Project Structure](#project-structure)
- [Pages Overview](#pages-overview)
- [Security Features](#security-features)
- [Admin Credentials](#admin-credentials)
- [Screenshots](#screenshots)
- [Author](#author)

---

## 📌 About the Project

VoteApp solves the problem of traditional paper-based voting which is slow, expensive and prone to fraud. This system allows users to register, login and cast secure votes from any device. Results are displayed live in real time using interactive charts.

**Key Goals:**
- Secure one-vote enforcement at both PHP and database level
- Live results with Chart.js updating every 10 seconds
- IP address tracking for every voter
- Admin dashboard with full election management
- Mobile responsive design

---

## ✨ Features

### 👤 User Features
- ✅ User registration with bcrypt password hashing
- ✅ Secure login with session management
- ✅ Vote with confirmation popup before submitting
- ✅ Countdown timer showing time remaining to vote
- ✅ Candidate photos displayed on vote page
- ✅ View live results with bar chart and comparison chart
- ✅ Winner announcement when election closes
- ✅ Share results link with one click
- ✅ Print results as PDF
- ✅ Profile page with edit info and change password
- ✅ Voting badge system (New Voter → Champion Voter)
- ✅ Voting history on profile page
- ✅ Contact us page with FAQ
- ✅ Dark mode toggle saved in browser

### ⚙️ Admin Features
- ✅ Admin dashboard with statistics
- ✅ Create, edit and delete elections
- ✅ Add candidates with photo upload
- ✅ Send announcements to all users
- ✅ IP address tracking for all voters
- ✅ Export election results as CSV
- ✅ Export voter list as CSV
- ✅ View contact messages from users
- ✅ Automatic election status update

### 🔒 Security Features
- ✅ bcrypt password hashing
- ✅ PDO prepared statements (SQL injection prevention)
- ✅ Double vote enforcement (PHP + MySQL UNIQUE constraint)
- ✅ IP address tracking on signup and voting
- ✅ Session-based authentication
- ✅ Role-based access control (Admin vs User)
- ✅ XSS prevention with htmlspecialchars()

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, Tailwind CSS, JavaScript (ES6) |
| Backend | PHP 8.0 |
| Database | MySQL 5.7 |
| Charts | Chart.js |
| Server | Apache (XAMPP) |
| Version Control | Git + GitHub |

---

## 🗄️ Database Schema

The database contains 6 tables:

| Table | Purpose |
|---|---|
| `users` | Registered users with roles and IP tracking |
| `elections` | Elections with status and date management |
| `candidates` | Candidates linked to elections with photos |
| `votes` | Vote records with IP tracking and UNIQUE constraint |
| `announcements` | Admin announcements shown on home page |
| `contacts` | Contact form messages from users |

---

## ⚙️ Setup Instructions

### Prerequisites
- XAMPP (Apache + MySQL + PHP 8.0)
- Web browser (Chrome recommended)
- Git (optional)

### Step 1 — Install XAMPP
Download from [https://www.apachefriends.org](https://www.apachefriends.org)
Start **Apache** and **MySQL** in XAMPP Control Panel

### Step 2 — Copy Project Files
```bash
# Copy the voting_system folder to:
C:\xampp\htdocs\voting_system\
```

### Step 3 — Create Database
1. Open `http://localhost/phpmyadmin`
2. Click **New** in left sidebar
3. Enter database name: `voting_system`
4. Set collation: `utf8mb4_unicode_ci`
5. Click **Create**

### Step 4 — Import Database
1. Click **voting_system** database
2. Click **SQL** tab
3. Paste the contents of `sql/schema.sql`
4. Click **Go**

### Step 5 — Configure Database
Open `config/db.php` and verify:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'voting_system');
define('DB_USER', 'root');
define('DB_PASS', '');  // blank for XAMPP default
```

### Step 6 — Open Application
http://localhost/voting_system

---

## 📁 Project Structure
voting_system/
├── admin/
│   ├── announcements.php    ← Manage announcements
│   ├── candidates.php       ← Add/remove candidates
│   ├── dashboard.php        ← Admin stats + management
│   ├── elections.php        ← Create/edit elections
│   └── export_csv.php       ← Export results as CSV
├── api/
│   └── results_data.php     ← JSON API for live charts
├── config/
│   └── db.php               ← Database connection (PDO)
├── includes/
│   ├── auth_guard.php       ← Login/admin protection
│   ├── footer.php           ← Shared footer
│   ├── get_ip.php           ← IP address helper
│   ├── header.php           ← Navbar + dark mode
│   └── update_status.php    ← Auto election status update
├── public/
│   └── uploads/             ← Candidate photos
├── sql/
│   └── schema.sql           ← Complete database schema
├── index.php                ← Home page
├── login.php                ← User login
├── signup.php               ← User registration
├── logout.php               ← Session destroy
├── vote.php                 ← Cast vote page
├── results.php              ← Live results + charts
├── profile.php              ← User profile + badges
├── contact.php              ← Contact us page
├── 404.php                  ← Custom error page
├── .htaccess                ← URL routing
├── .gitignore               ← Git ignore rules
└── README.md                ← This file

---

## 📄 Pages Overview

| Page | URL | Description |
|---|---|---|
| Home | `/index.php` | Elections list, announcements, stats |
| Sign Up | `/signup.php` | New user registration |
| Login | `/login.php` | User authentication |
| Vote | `/vote.php?id={id}` | Cast ballot with countdown timer |
| Results | `/results.php?id={id}` | Live charts and winner announcement |
| Profile | `/profile.php` | Edit info, badges, voting history |
| Contact | `/contact.php` | Contact form and FAQ |
| Admin Dashboard | `/admin/dashboard.php` | Admin management panel |
| Admin Elections | `/admin/elections.php` | Create and edit elections |
| Admin Candidates | `/admin/candidates.php` | Manage candidates |
| Announcements | `/admin/announcements.php` | Send notices to users |
| 404 | `/404.php` | Custom not found page |

---

## 🔒 Security Features

### Password Hashing
```php
// Never stored as plain text
$hashed = password_hash($password, PASSWORD_DEFAULT);
password_verify($input, $hashed);
```

### SQL Injection Prevention
```php
// Always using prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

### Double Vote Enforcement
```sql
-- Database level constraint
UNIQUE KEY one_vote_per_election (user_id, election_id)
```

### IP Address Tracking
```php
// Captured on signup and every vote
function get_ip_address() {
    return $_SERVER['HTTP_CLIENT_IP']
        ?? $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['REMOTE_ADDR'];
}
```

## 🔑 Admin Credentials
Email:    admin@voteapp.com
Password: Admin@123

> ⚠️ Change these credentials before deploying to production

## 👩‍💻 Author

**Monisha Hossain**
- Institution: Metropoliton University, Sylhet
- Email: monishahossain75@gmail.com
- GitHub: [github.com/monisha0665](https://github.com/monisha0665)
- Project: [github.com/monisha0665/voting-system](https://github.com/monisha0665/voting-system)

## 📊 Project Stats

| Metric | Count |
|---|---|
| Total Pages | 12 |
| Database Tables | 6 |
| Git Commits | 20+ |
| Features | 30+ |
| Lines of Code | ~3000+ |

---

## 📝 License

This project was built as a final web application project for the Web Development course at Al Emdad College, Sylhet, Bangladesh.

---

*Built with ❤️ using PHP + MySQL + Tailwind CSS*