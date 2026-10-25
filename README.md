# 🏢 CoSpace

**Coworking Room & Reservation Management System** — a PHP/MySQL web application with an integrated AI Assistant (Python FastAPI + Google Gemini) and automated SMTP email notifications.

CoSpace lets administrators manage rooms, employees, and reservations from a central dashboard, while employees and members can browse rooms, book 2-hour time slots, and use a multilingual AI assistant from their personal workspace.

---

## 🚀 Features

- **3 User Roles**: `administrateur` (admin), `employe` (employee), and `membre` (member)
- **Authentication**: Login & registration with hashed passwords (`password_hash` / `password_verify`)
- **Admin Management (CRUD)**: Create, edit, and delete rooms and employees
- **Room Reservations**: Book rooms in 2-hour time slots between `08:00` and `18:00` (`08:00–10:00`, `10:00–12:00`, `12:00–14:00`, `14:00–16:00`, `16:00–18:00`)
- **24-Hour Cancellation Rule**:
  - Members and employees can cancel their own reservations only if at least **24 hours** remain before the slot's actual start time (`date_reservation` + `heure_debut`).
  - Administrators can cancel any reservation at any time (with a required reason and audit log when cancelling via the AI assistant).
- **Email Notifications (Gmail SMTP)**:
  - Automatic HTML emails sent on **user registration**, **new reservation confirmation**, and **reservation cancellation**.
- **Integrated AI Assistant (FastAPI + Gemini)**:
  - Floating chat widget available on all authenticated pages (matches Dark/Light mode).
  - **Multilingual**: Replies in **French, Arabic (with automatic right-to-left RTL rendering), and English**.
  - Answers questions using verified CoSpace rules and live room/equipment data without inventing policies or prices.
  - Retrieves only the logged-in user's own reservations (or global statistics for administrators).
  - Supports **2-step reservation cancellation** in chat with an explicit confirmation button (`❌ Confirmer l'annulation`) and full server-side validation in PHP.
  - Keeps temporary conversation memory only for the active login session (no chat history stored in the database).
- **Interactive Calendar**: Monthly reservation view with detail modals
- **QR Codes**: Generates a room check-in QR code for reservations
- **Admin Dashboard**: Live statistics (Chart.js) and user feedback ratings
- **Dark / Light Theme**: Saved in the browser (`localStorage`)

---

## 🧱 Architecture

```text
index.php                       ← Main entry point & router (?page=...)
connexion/database.php          ← Database credentials (ignored by Git)
connexion/mailer.php            ← Zero-dependency Gmail SMTP HTML email helper
models/                         ← PDO database models (User, Room, Reservation, Creneaux, Employee)
controllers/                    ← Business logic (Auth, Admin, Room, Employee, Reservation, Assistant, ...)
views/                          ← PHP/HTML templates (admin / member / auth / profile / calendar / layouts)
public/css/style.css            ← Global stylesheet (Dark/Light themes + floating AI chat widget)
public/js/app.js                ← Client-side JS (sidebar, alerts, theme toggle, AI chat widget)
ai_service/                     ← Python FastAPI microservice
├── main.py                     ← /health and /ask endpoints talking to Google Gemini
├── requirements.txt            ← Python package dependencies
└── .env.example                ← Template for API & SMTP environment variables
```

### How the AI Assistant Stays Secure
The browser never talks directly to FastAPI or Gemini, and the AI model never connects to the MySQL database:
```text
Browser ──(session cookie)──► PHP (AssistantController.php) ──(cURL)──► FastAPI (main.py) ──► Gemini API
```
PHP verifies the user's session and role first, queries only the data that user is allowed to see, and sends the minimal context to FastAPI (`127.0.0.1:8001`). Every cancellation click is re-validated on the PHP server.

---

## ⚙️ Setup & Installation

### 1. Prerequisites
- **PHP 8+** (with `pdo_mysql`, `curl`, and `openssl` enabled)
- **MySQL** (configured on port `3308` by default)
- **Python 3.10+** (for the FastAPI AI service)
- Local web server (**WAMP**, XAMPP, or MAMP)

### 2. Database & PHP Configuration
1. Create a MySQL database named `bd_cospace` and import the project SQL schema.
2. Create `connexion/database.php` with your local MySQL connection constants (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET`).

### 3. AI Service & Email Configuration (`ai_service/`)
1. Open a terminal inside `ai_service/`, create the virtual environment, and install dependencies:
   ```cmd
   cd ai_service
   python -m venv .venv
   .venv\Scripts\activate.bat
   pip install -r requirements.txt
   ```
2. Copy `ai_service/.env.example` to `ai_service/.env` and fill in your credentials:
   ```env
   GEMINI_API_KEY=your_gemini_api_key_here
   GEMINI_MODEL=gemini-flash-lite-latest
   SMTP_USER=your_email@gmail.com
   SMTP_PASS=your_16_char_google_app_password
   ```
3. Start the FastAPI server:
   ```cmd
   uvicorn main:app --host 127.0.0.1 --port 8001
   ```

### 4. Demo Accounts

| Role          | Login             | Password     |
|---------------|-------------------|--------------|
| Administrator | `admin`           | `admin123`   |
| Employee      | `jean.dupont`     | `employe123` |
| Member        | `marie@gmail.com` | `membre123`  |

---

## 🛡️ Security
- Passwords hashed with `password_hash()` and verified with `password_verify()`
- Prepared SQL statements via PDO
- Role-based access control (`requireRole`) and server-side ownership checks
- Secrets (`connexion/database.php` and `ai_service/.env`), logs (`*.log`), and virtual environments (`ai_service/.venv/`) excluded via `.gitignore`

---

## 📝 License
Educational project — free to use.
