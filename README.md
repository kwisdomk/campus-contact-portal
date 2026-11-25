# Campus contact

Simple contact management web app built with plain PHP, MySQL (PDO), HTML, and CSS for BSD 322.

## Setup

1. Start Apache and MySQL in XAMPP (or your local PHP/MySQL setup).
2. Put this folder in `htdocs/campus_contact_portal/`.
3. Open phpMyAdmin (`http://localhost/phpmyadmin/`), open the SQL tab, and import `sql/campus_contact_db.sql` once.
4. Copy `config.example.php` to `config.php` and update `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` if needed (XAMPP usually uses `root` with an empty password).
5. Open `http://localhost/campus_contact_portal/`.
6. Register an account or sign in with the local test user (`sample@example.test` / `CampusDemo2026!`).

## Features

- Registration, login, logout, and session timeout (`password_hash`, `password_verify`)
- Dashboard with message totals and recent submissions
- Profile page for updating username and email
- Contact form with server-side validation and subject categories
- Submissions page with search, sorting, pagination (5 per page), edit/delete, and CSV import/export
- Info page showing session/cookie details and message counts per subject
- CSRF tokens on forms, HTML escaping, and activity logging (`data/activity.log`)

## Files

- `config.example.php` - sample database and session settings
- `functions.php`, `layout.php` - shared helpers, validation, CSRF, and layout
- `login.php`, `register.php`, `logout.php` - auth pages
- `dashboard.php`, `profile.php`, `contact.php`, `submissions.php`, `info.php` - app pages
- `sql/campus_contact_db.sql` - database schema and sample rows
- `assets/` - stylesheet and logo
- `docs/` - ERD, checklist, and report draft

## Notes

- `sql/.htaccess` and `data/.htaccess` block direct HTTP access under Apache.
- Remove the sample user in `sql/campus_contact_db.sql` and use HTTPS if hosting publicly.
