# Frowear Productions — Platform V2

A full-stack PHP/MySQL platform for project collaboration, talent networking, and contract work. Built on vanilla PHP and JavaScript with no framework dependencies — deployable to shared hosting (Hostinger).

---

## What's included

### Marketing site (`index.php`)
Admin-editable portfolio and inquiry site covering company info, projects, services, skills, opportunities, companies, and talent profiles. Supports theme customisation and image uploads.

### Platform app (`platform.php`)
An authenticated single-page application for:

- **Feed** — LinkedIn-style post feed with 9 post types: Update, Opportunity, Project, Event, Collaboration, Skill Share, News, Celebration, Achievement. Reactions, comments, visibility controls.
- **Messages** — Direct and group conversations with real-time-ready threads, read receipts, and unread badge counts.
- **Bids** — Place bids on projects and opportunities. Accept/decline incoming bids. Auto-generates a contract draft on acceptance.
- **Notifications** — In-app and email notifications for messages, bids, reactions, comments, and application updates.
- **Profiles** — Talent and company profiles with skills, availability, and recent posts.

---

## Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.0+ (vanilla, no framework) |
| Database | MySQL 8.0 / MariaDB 10.6 |
| Frontend | Vanilla JS (no build step) |
| Styling | CSS custom properties, no preprocessor |
| Auth | PHP sessions + bcrypt |
| Storage | MySQL primary, JSON file fallback |
| Deploy | Apache shared hosting (Hostinger) |

---

## Project structure

```text
.
├── index.php                  # Marketing / portfolio site
├── platform.php               # Authenticated platform app (SPA shell)
├── admin.php                  # CMS admin panel
├── webhook.php                # Inbound webhook listener
│
├── api/
│   ├── auth/                  # register, login, logout, me, verify, forgot, reset
│   ├── feed/                  # posts, post, react, comments
│   ├── messages/              # conversations, thread, send, read
│   ├── bids/                  # index, update
│   ├── notifications/         # index, read, counts
│   └── profiles/              # talent, company
│
├── includes/
│   ├── bootstrap.php          # DB connection, env helpers, shared functions
│   ├── auth.php               # User auth, sessions, token lifecycle
│   ├── db.php                 # PDO query helpers
│   ├── mail.php               # HTML email via PHP mail()
│   └── notify.php             # Notification creation and queuing
│
├── sql/
│   ├── platform_schema.sql    # V1 schema (run first)
│   └── v2_schema.sql          # V2 migration — users, feed, messages, bids, events, notifications
│
├── icons/
│   ├── icon.svg               # PWA app icon
│   └── icon-maskable.svg      # Maskable variant for Android
│
├── uploads/                   # User-uploaded images (WebP, max 700 KB)
├── data/                      # JSON content fallback + webhook log
├── manifest.json              # PWA manifest
├── .htaccess                  # Security headers, directory protection
├── platform.js                # Platform SPA logic
├── platform.css               # Platform component styles
├── script.js                  # Marketing site JS
├── style.css                  # Shared design system
└── .env                       # Environment secrets (not committed)
```

---

## Setup

### 1. Environment

Copy `.env.example` to `.env` and fill in your values:

```env
FW_ADMIN_KEY=replace-with-a-strong-secret
FW_WEBHOOK_SECRET=replace-with-a-strong-webhook-secret

DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password

APP_URL=https://yourdomain.com

MAIL_FROM_ADDRESS=hello@yourdomain.com
MAIL_FROM_NAME=Frowear Productions
```

### 2. Database

Run both SQL files in order using phpMyAdmin or your MySQL client:

```bash
# 1 — V1 tables (site_content, companies, talent, projects, opportunities, etc.)
mysql -u user -p dbname < sql/platform_schema.sql

# 2 — V2 tables (users, feed, messages, bids, events, notifications, + alters V1)
mysql -u user -p dbname < sql/v2_schema.sql
```

### 3. Deploy

Upload all files to your Hostinger public_html folder. The `.htaccess` handles security headers and directory protection automatically.

Make sure PHP has write access to `data/` and `uploads/`.

### 4. First login

Visit `https://yourdomain.com/platform.php`, register an account, then verify your email. The marketing site remains at `/index.php`.

---

## Local development

```bash
php -S 127.0.0.1:8005
```

Then open:
- `http://127.0.0.1:8005/index.php` — marketing site
- `http://127.0.0.1:8005/platform.php` — platform app

---

## API reference

All endpoints return `{"ok": true|false, "data": {...}, "message": "..."}`.

| Method | Endpoint | Description |
|---|---|---|
| POST | `api/auth/register` | Create account |
| POST | `api/auth/login` | Sign in |
| POST | `api/auth/logout` | Sign out |
| GET | `api/auth/me` | Current user + unread count |
| GET | `api/auth/verify?token=` | Email verification link |
| POST | `api/auth/forgot` | Send password reset |
| GET/POST | `api/auth/reset` | Password reset form + submit |
| GET/POST | `api/feed/posts` | List feed / create post |
| DELETE | `api/feed/post?id=` | Delete own post |
| POST | `api/feed/react` | Toggle reaction |
| GET/POST | `api/feed/comments` | List / add comment |
| GET/POST | `api/messages/conversations` | List / create conversation |
| GET | `api/messages/thread?conversation_id=` | Load messages |
| POST | `api/messages/send` | Send message |
| POST | `api/messages/read` | Mark messages read |
| GET/POST | `api/bids/index` | List bids / place bid |
| POST | `api/bids/update` | Accept, decline, or withdraw bid |
| GET | `api/notifications/index` | List notifications |
| POST | `api/notifications/read` | Mark read |
| GET | `api/notifications/counts` | Badge counts (poll every 30s) |
| GET/PUT | `api/profiles/talent` | View / update talent profile |
| GET/PUT | `api/profiles/company` | View / update company profile |

---

## Image uploads

Images are compressed client-side to WebP before upload:

- Max dimension: 2400px (auto-scaled)
- Max file size: 700 KB (quality stepped down automatically)
- Upload via the admin panel image fields or the feed post composer

Uploaded files land in `/uploads/` with random filenames. PHP execution is blocked in that directory by its own `.htaccess`.

---

## PWA

`manifest.json` enables "Add to Home Screen" on iOS and Android:

- Display: `standalone` (fullscreen, no browser chrome)
- Theme: `#4be7ff` (cyan)
- Background: `#040914` (dark)
- Icons: SVG (scales to any size)

---

## Webhook listener

```text
POST /webhook.php
Header: X-Webhook-Secret: your-secret
```

Valid requests are appended to `data/webhook-requests.log` as newline-delimited JSON.

---

## Validation

```bash
# PHP syntax check
find . -name "*.php" -not -path "./.git/*" | xargs -I{} php -l {}

# JS syntax check
node --check script.js
node --check platform.js
```

---

## Roadmap

- **Real-time messages** — replace 30s badge polling with Server-Sent Events
- **Contract milestones UI** — milestone approval flow in the bids view
- **Events** — event creation, RSVP, and feed integration
- **Company admin panel** — dedicated surface for company owners
- **Email queue worker** — PHP CLI cron to flush `notifications WHERE email_sent = 0`
- **Search** — full-text search across posts, profiles, and opportunities
