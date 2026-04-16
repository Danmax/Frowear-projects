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
├── style.css
└── .env
```

## How It Works

`index.php` loads the current site content from `data/content.json`. If that file does not exist yet, the app uses the defaults defined in `includes/bootstrap.php`.

`admin.php` handles:

- admin login
- admin logout
- content save
- reset to defaults

The browser never gets the real `FW_ADMIN_KEY`. PHP reads it from the server environment or `.env` file and verifies login on the server.

## Hostinger Setup

1. Upload the project files to your hosting folder.
2. Make sure `index.php` is the page being loaded.
3. Create a `.env` file in the project root.
4. Add your admin key:

```env
FW_ADMIN_KEY=replace-with-a-strong-secret-key
```

5. Make sure the `data/` folder is writable by PHP.
6. Open the site and use the `Admin` button to log in.

## Content Storage

Saved admin changes are written to:

```text
data/content.json
```

That means:

- changes persist after reload
- changes persist across devices
- changes do not depend on browser local storage

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
php -l includes/bootstrap.php
node --check script.js
```

## Notes

- This uses PHP sessions for admin authentication.
- The quote form is still frontend-only and currently shows a confirmation message in the UI.
- If you want production quote handling, add a mailer, database, or CRM endpoint next.
