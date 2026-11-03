# 🏛️ ViretaDev - Smart Union Governance Platform

**ViretaDev** is a multi-tenant SaaS portal specifically designed for Bangladeshi Union Parishads. It streamlines local government administration, holding tax management, and digital certificate issuance through a modern, scalable cloud architecture.

---

## 🚀 Key Features

- **Multi-Tenant Architecture:** Single installation serving multiple Union Parishads with complete data isolation.
- **Holding Tax Automation:** Digital assessment, billing, and instant money receipt printing.
- **Digital Certificates:** One-click generation of Character, Heirship, and Unmarried certificates with QR-code verification.
- **Citizen Database:** Comprehensive digital record of families and members including NID and professional data.
- **Trade License Management:** Digital issuance and annual renewal of business licenses.
- **Role-Based Access:** Secure access levels for Chairman, Secretary, and Digital Center Entrepreneurs.
- **Branded Portals:** Each Union gets its own dedicated portal with custom branding and sub-domains.

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP 8.5 + CodeIgniter 3 |
| **Database** | PostgreSQL |
| **Frontend** | Tailwind CSS v4 + Phosphor Icons |
| **Security** | Custom WAF + IP Protection + Bcrypt Encryption |
| **Server** | Nginx / Apache (cPanel compatible) |

---

## 📦 Installation & Setup

### 1. Prerequisites
- PHP 8.5+ with `pdo_pgsql` extension.
- PostgreSQL Database.
- Web server (Nginx or Apache).

### 2. Local Setup
1. **Clone the Repository:**
   ```bash
   git clone https://github.com/Vireta-Group/union-ht.git
   cd union-ht
   ```
2. **Configure Environment:**
   Copy `.env.example` to `.env` and fill in your database and app credentials:
   ```bash
   cp .env.example .env
   ```
3. **Database Setup:**
   Import the migrations located in `migrations/*.sql` to your PostgreSQL database.
4. **Permissions:**
   Ensure the `uploads/` folder is writable:
   ```bash
   chmod -R 775 uploads/
   ```
5. **Run Server:**
   ```bash
   php -S localhost:8080 router.php
   ```

---

## ⚙️ Deployment (Production)

For cPanel/VPS deployment, refer to the detailed guide in `deploy/DEPLOY.md`.

**Important Production Check:**
- Set `APP_ENV=production` in `.env`.
- Generate a 32+ character `APP_ENCRYPTION_KEY`.
- Set `APP_TENANT_APEX` to your primary domain (e.g., `viretaup.com`).

---

## 🛡️ Security Notes

- **Data Isolation:** The `Tenant_resolver` ensures that users from one Union cannot access data from another.
- **Sanitization:** All inputs are filtered through CI3's XSS filtering and PDO prepared statements to prevent SQL Injection.
- **Encryption:** Sensitive data is encrypted using the key defined in the `.env` file.

---

## 📄 License
This project is proprietary. All rights reserved to **ViretaDev Technologies**.

---

**Developed with ❤️ by ViretaDev Team**

---
**🚀 Deployment Status:**
- CI/CD: Enabled via GitHub Actions
- Environment: Production (cPanel)
- Last Automated Update: 2026-11-04

