# Frowear Projects

Portfolio and inquiry site for Frowear Productions with a PHP-backed admin panel for editing:

- Company Info
- Projects
- Services
- Skills
- Opportunities
- Branding
- Theme

The public site is rendered by `index.php`. Admin authentication and content persistence are handled by PHP so the site works on shared hosting such as Hostinger.

## Stack

- PHP
- HTML
- CSS
- JavaScript
- JSON file storage

## Project Structure

```text
.
├── admin.php
├── data/
│   └── content.json        # created after first save
├── includes/
│   └── bootstrap.php
├── index.php
├── script.js
├── sql/
│   └── platform_schema.sql
├── style.css
├── webhook.php
└── .env
```

## How It Works

`index.php` now prefers MySQL for site content storage. If the database is unavailable, the app falls back to `data/content.json`. If neither exists yet, it uses the defaults defined in `includes/bootstrap.php`.

`admin.php` handles:

- admin login
- admin logout
- content save
- reset to defaults

The browser never gets the real `FW_ADMIN_KEY`. PHP reads it from the server environment or `.env` file and verifies login on the server.

`webhook.php` handles inbound webhooks with a shared secret. Valid requests are appended to `data/webhook-requests.log` as JSON lines.

`sql/platform_schema.sql` provides a MySQL-ready schema for:

- `site_content` for the current admin-managed content store
- companies
- talent profiles
- skills
- opportunities
- applications
- project assignments

## Hostinger Setup

1. Upload the project files to your hosting folder.
2. Make sure `index.php` is the page being loaded.
3. Create a `.env` file in the project root.
4. Add your keys and database credentials:

```env
FW_ADMIN_KEY=replace-with-a-strong-secret-key
FW_WEBHOOK_SECRET=replace-with-a-strong-webhook-secret
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
```

5. Import `sql/platform_schema.sql` into your MySQL database with phpMyAdmin.
6. Make sure the `data/` folder is writable by PHP for fallback storage and webhook logs.
7. Open the site and use the `Admin` button to log in.

## Content Storage

Saved admin changes are written to MySQL when database access is configured:

```text
site_content
```

Webhook requests are appended to:

```text
data/webhook-requests.log
```

That means:

- changes persist after reload
- changes persist across devices
- changes do not depend on browser local storage
- changes use MySQL first, with JSON file fallback if the DB is unavailable

## Local Development

Run a local PHP server from the project root:

```bash
php -S 127.0.0.1:8005
```

Then open:

```text
http://127.0.0.1:8005/index.php
```

## Validation

These checks were used during development:

```bash
php -l index.php
php -l admin.php
php -l webhook.php
php -l includes/bootstrap.php
node --check script.js
```

## Webhook Listener

Endpoint:

```text
/webhook.php
```

Authentication:

- Set `FW_WEBHOOK_SECRET` in `.env`
- Send the same value in the `X-Webhook-Secret` header

Accepted methods:

- `GET` for a simple readiness check
- `POST` for webhook delivery

Example:

```bash
curl -X POST https://your-domain.com/webhook.php \
  -H "Content-Type: application/json" \
  -H "X-Webhook-Secret: your-webhook-secret" \
  -H "X-Webhook-Event: project.updated" \
  -d '{"project":"Frowear","status":"updated"}'
```

## Notes

- This uses PHP sessions for admin authentication.
- The quote form is still frontend-only and currently shows a confirmation message in the UI.
- If you want production quote handling, add a mailer, database, or CRM endpoint next.
