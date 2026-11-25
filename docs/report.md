# BSD 322 Internet Programming II - CAT 3 project report

**Project:** Campus contact  
**Group name:** JeffWisdom  
**Members:** Jefferson Mwangi, Wisdom Kinoti


## 1. Overview

This project is a contact management site built with HTML, CSS, core PHP, and MySQL. Users can register, log in, and submit messages through a contact form. Once signed in, they can view, search, sort, edit, and delete their messages, and import or export messages via CSV.

## 2. Database design

The database uses three tables: `users`, `categories`, and `messages`. One user can submit many messages, and each message belongs to one category. Usernames and emails are unique, and foreign keys keep references valid.

```text
users (id PK) 1 -------- * messages (id PK, user_id FK, category_id FK)
                                   *
                                   |
                                   1
                             categories (id PK)
```

*Screenshot: phpMyAdmin tables and foreign keys.*

## 3. How it works

Apache receives browser requests and runs the requested PHP script. Protected pages check the session, validate form input, and query MySQL with PDO. Output is rendered to HTML and sent back to the browser. Passwords are stored with `password_hash()` and checked with `password_verify()`.

*Screenshot: registration, login, dashboard, and contact form.*

## 4. Sessions, cookies and forms

PHP sessions keep the user signed in across protected pages. Logging out clears the session, expires the session cookie, and destroys the session. The `last_login` cookie only stores the last login timestamp, with no passwords or user IDs. GET handles search, sorting, and pagination; POST handles registration, login, profile updates, and message edits or deletions.

## 5. Validation and security

Required fields, email format, string lengths, and valid category IDs are validated on the server. All queries use PDO prepared statements. User text is escaped with `htmlspecialchars()` before rendering. Forms that change data include a CSRF token, and protected pages require login.

*Screenshot: validation errors, escaped output, and login redirect.*

## 6. PHP features used

The code uses indexed and associative arrays, loops, conditionals, helper functions, string and array functions, sessions, cookies, PDO queries (`INNER JOIN`, `LEFT JOIN`, `GROUP BY`, aggregates), and file handling (CSV import/export and activity logging).

## 7. Local setup and hosting

The app was tested locally with XAMPP (Apache and MySQL). Copy the folder into `htdocs`, import `sql/campus_contact_db.sql` in phpMyAdmin, copy `config.example.php` to `config.php`, and open the site in a browser.

| Option | Advantage | Limitation | Fit |
|---|---|---|---|
| Shared PHP/MySQL hosting (e.g., InfinityFree) | Public URL with PHP and MySQL included | Free-tier resource limits and config restrictions | Good for public demo |
| Shared PHP/MySQL hosting (e.g., AwardSpace) | Public hosting without server setup | Free-tier storage and feature limits | Alternative option |

Local XAMPP was used for development and testing because it works offline and resets quickly.

## 8. Framework comparison

| Technology | Summary | Why not used here |
|---|---|---|
| Laravel | Full PHP framework with routing and ORM | Extra setup beyond a small coursework project |
| Django | Python web framework with built-in tools | Uses Python instead of PHP |
| Node.js | JavaScript server runtime | Different backend stack |
| Flask | Lightweight Python web framework | Does not demonstrate core PHP directly |

Plain PHP keeps the focus on core syntax, forms, sessions, and PDO queries covered in class.

## 9. SEO and accessibility

- [x] Page titles and meta descriptions verified
- [x] Semantic `<header>`, `<nav>`, `<main>`, and `<footer>` tags verified
- [x] Open Graph metadata verified (update image URL if hosted publicly)
- [x] Clear headings and navigation verified
- [x] Responsive layout tested on desktop and mobile widths

## 10. Testing and conclusion

[Add test results and screenshots for registration, login, submission, editing, deleting, filtering, pagination, CSV import/export, and logout.]

Building the app tied together server-side validation, prepared SQL queries, sessions, and CRUD operations in plain PHP. Keeping the layout straightforward made testing and debugging easier.

## 11. Screenshots

1. Database schema and sample rows
2. Registration and login
3. Dashboard and navigation
4. Contact form and saved message
5. Search, pagination, edit, and delete
6. CSV import/export, cookie, and session checks
7. Local setup and HTML metadata
