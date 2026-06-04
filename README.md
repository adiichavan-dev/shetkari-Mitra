<div align="center">

# 🌾 Shetkari Mitra — शेतकरी मित्र

**A Farmer Management System built with PHP & MySQL**

*Designed for farmers of the Kolhapur & Sangli region, Maharashtra*

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

</div>

---

## 📖 About the Project

**Shetkari Mitra** (meaning *Farmer's Friend* in Marathi) is a web-based farm management system developed as a college mini project. It helps farmers manage their daily milk production, crop sales, billing, customer records, and payment tracking — all from one simple dashboard.

The farmer has full control over all data — customers, rates, bills, payments, and livestock records. The system supports a **bilingual UI** (English & Marathi) and uses real Kolhapur Jilha Sahakari Dudh Sangh rate charts for automatic FAT/SNF-based milk rate calculation.

---

## ✨ Features

| Module | Description |
|--------|-------------|
| 🐄 **Dairy & Livestock** | Separate cow & buffalo milk tracking with morning/evening entry and FAT/SNF auto rate calculation |
| 🌾 **Crop Management** | Record crop harvests, sales, and land records |
| 🧾 **Milk Bills** | Auto-generate bills from dairy entries, mark paid/unpaid |
| 🧾 **Crop Bills** | Bill generation for crop sales |
| 💰 **Payments** | Track cash, UPI, online, and cheque payments — linked to bills |
| 👥 **Customers** | Manage retail and company milk customers |
| 📊 **Rate Management** | Editable cow & buffalo rate charts (FAT × SNF grid) |
| 📱 **Bilingual UI** | English and Marathi language toggle |
| 🖨️ **Print Reports** | Print-ready bill and payment reports |

---

## 🖥️ Screenshots
<img width="1036" height="520" alt="image" src="https://github.com/user-attachments/assets/721e2ef7-b805-4116-a97b-03f830b6e853" />
<img width="1011" height="514" alt="image" src="https://github.com/user-attachments/assets/36ba40cb-9abf-4393-948e-129ac8fe6e06" />
<img width="982" height="496" alt="image" src="https://github.com/user-attachments/assets/4b8a0a77-8f2c-4e9c-8970-a226c15d925a" />
<img width="984" height="497" alt="image" src="https://github.com/user-attachments/assets/6109a9b0-1d70-484e-bd82-d82c61afdaca" />
<img width="1006" height="512" alt="image" src="https://github.com/user-attachments/assets/b764c41e-69bf-419a-9b44-a5aebdca8ffb" />
<img width="1072" height="542" alt="image" src="https://github.com/user-attachments/assets/83c1d137-3e9c-4c64-9ac0-b5d4f2440809" />
<img width="1017" height="514" alt="image" src="https://github.com/user-attachments/assets/d2dd4c86-4f95-4099-b732-7b68eef3c69c" />
<img width="1006" height="512" alt="image" src="https://github.com/user-attachments/assets/badcac7a-bf17-439b-882a-245ffff29d19" />


---

## 🏗️ Project Structure

```
shetkari_mitra/
│
├── index.php               # Login redirect
├── login.php               # Login page
├── dashboard.php           # Main dashboard
├── logout.php              # Session logout
├── customer.php            # Customer-facing bill portal
├── database.sql            # Full MySQL database schema + sample data
├── setup_admin.php         # First-time account setup
│
├── api/                    # AJAX backend endpoints (PHP)
│   ├── auth.php            # Login / session handling
│   ├── bills.php           # Bill CRUD + quick pay
│   ├── crops.php           # Crop records API
│   ├── customers.php       # Customer management API
│   ├── dairy.php           # Milk entries + livestock API
│   ├── dashboard.php       # Dashboard stats API
│   ├── payments.php        # Payment records API
│   ├── profile.php         # Profile update API
│   └── rates.php           # Custom rate chart API
│
├── pages/                  # UI pages (PHP + HTML)
│   ├── dairy.php           # Dairy & livestock page
│   ├── crops.php           # Crop management page
│   ├── milk_bills.php      # Milk bills page
│   ├── crop_bills.php      # Crop sales bills page
│   ├── payments.php        # Payment records page
│   ├── customers.php       # Customers page
│   ├── rates.php           # Rate chart editor
│   └── profile.php         # Profile settings
│
├── includes/               # Shared PHP components
│   ├── db.php              # MySQL database connection
│   ├── auth.php            # Session guard
│   ├── sidebar.php         # Navigation sidebar
│   └── head.php            # HTML head / meta
│
├── css/
│   └── style.css           # Main stylesheet (custom design system)
│
└── js/
    ├── app.js              # Core JS utilities (fetch, modals, toasts)
    ├── lang.js             # Bilingual translation strings (EN / Marathi)
    └── rates.js            # COW_RATES & BUFFALO_RATES lookup tables
```

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.x or above
- MySQL 8.x or above
- A local server like **XAMPP**, **WAMP**, or **Laragon**

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/adiichavan-dev/shetkari-mitra.git
```

**2. Move to your server's web root**
```
# For XAMPP:
Copy the folder to:  C:/xampp/htdocs/shetkari_mitra/

# For WAMP:
Copy the folder to:  C:/wamp64/www/shetkari_mitra/
```

**3. Create the database**
- Open **phpMyAdmin** → `http://localhost/phpmyadmin`
- Create a new database named `shetkari_mitra`
- Click **Import** → select `database.sql` → click Go

**4. Configure the database connection**

Open `includes/db.php` and update your credentials:
```php
$conn = new mysqli('localhost', 'root', 'YOUR_PASSWORD', 'shetkari_mitra');
```

**5. Run the project**
```
http://localhost/shetkari_mitra/
```

---

## 🔑 Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Farmer | *(8625805691)* | `mh2452` |
| Customer | *(7040000100)* | *(7040000100)* |

> ⚠️ **Change your password from the Profile page after first login.**

---

## 👥 User Roles

```
Farmer
 └── Full access — manage crops, dairy, customers, bills, payments, rate charts

Customer
 └── View-only portal — can see their own bills
```

---

## 🥛 Milk Rate System

The system uses rate charts :

- **Cow Milk** — FAT 3.0–5.0 | SNF 8.2–9.0 (Effective 01/04/2025)
- **Buffalo Milk** — FAT 4.5–10.0 | SNF 8.6–10.0 (Base: 11/09/2023 )

Rate is auto-calculated when FAT% and SNF% are entered in a milk entry. The farmer can view and edit both rate charts anytime from the **Rate Management** page.

---

## 💡 How the Payment System Works

1. Farmer adds a milk or crop entry → a **bill is auto-created**
2. Payment is **automatically recorded** in the Payments section
3. The Payments page shows **monthly totals** — Total Billed, Total Paid, Balance Due
4. Filter payments by **Milk** or **Crop** category, or view a **per-customer balance** breakdown

---

## 🛠️ Tech Stack

- **Backend:** PHP 8 (no framework — pure PHP)
- **Database:** MySQL with MySQLi
- **Frontend:** Vanilla HTML, CSS, JavaScript (no React/Vue)
- **Icons:** Font Awesome 6.5
- **Fonts:** DM Sans, Playfair Display (Google Fonts)
- **Architecture:** AJAX-based SPA-style pages with JSON API endpoints

---

## 📚 College Project Info

> This project was developed as a **Mini Project** for a college course on Web Technologies / PHP.
> It demonstrates full-stack PHP development including session management, AJAX APIs, dynamic UI, and MySQL database design.

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

<div align="center">
Made with ❤️ for Maharashtra's farmers &nbsp;|&nbsp; शेतकऱ्यांसाठी
</div>
