# Personal Expense Management System (PEMS)

A web-based application for recording, managing and analyzing personal income and expenses.
BCA Project I, Tribhuvan University, Triton International College.
Submitted by Aswin Adhikari and Manash Kadel.

## Features

| Module | What the user can do |
|---|---|
| User Management | Register, login, logout, change name and password |
| Category Management | Add, edit, delete categories (expense and income); mark a category Inactive |
| Income / Expense Management | Add, edit, delete transactions; every record has a category, amount, date and description |
| Search and Filter | Filter by type, category, date range and description text; paging for long lists |
| Dashboard | Total income, total expenses, current balance, this month's summary, recent transactions |
| Reports | Monthly summary of a year (income, expenses, balance) and category-wise expense summary |
| Charts | Income vs expenses bar chart and expense distribution doughnut chart |
| Budget Monitoring | Set a monthly budget for an expense category and compare it with actual spending |

Balance = Total Income - Total Expenses. All numbers come from the database.

## Technology stack

- Frontend: HTML5, CSS3, JavaScript (no framework)
- Backend: PHP with PDO and prepared statements, PHP sessions
- Database: MySQL / MariaDB (XAMPP)
- Chart library: Chart.js 4.4.7 (MIT license), stored locally in `assets/js/chart.umd.js`, so no internet is needed
- Tools: XAMPP, VS Code, phpMyAdmin

## Requirements

- XAMPP with Apache and MySQL (PHP 7.4 or newer; developed and tested with PHP 8.x and MariaDB 10.x)
- A modern web browser (Chrome, Edge or Firefox)
- PHP extensions `pdo_mysql` and `mbstring` (both are enabled in a default XAMPP)

## Installation

1. Copy the `PEMS` folder to `C:\xampp\htdocs\` so that `C:\xampp\htdocs\PEMS\index.php` exists.
2. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. Open http://localhost/phpmyadmin, click **Import**, choose `database/database.sql` and click **Import**.
   This creates the database `pems_db` with the 4 tables.
4. (Optional) Import `database/demo_data.sql` to get a demo user with sample data (see below).
5. Open http://localhost/PEMS/ (if your Apache uses another port, for example 8080, open http://localhost:8080/PEMS/).
6. Click **Create an account**, register and log in.

If your project folder is not named `PEMS`, or your database password is not empty, edit `config/config.php`
(`BASE_URL`, `DB_USER`, `DB_PASS`).

## Demo account (optional)

After importing `database/demo_data.sql`:

- Email: `demo@pems.test`
- Password: `Demo@1234`

It contains about 4 months of sample transactions and budgets for the current month (dates are relative to today).
It is only sample data: the system works without it, and new users start with an empty account and the default categories.
To remove it, import `database/remove_demo_data.sql` in phpMyAdmin.

## Project structure

```
PEMS/
|-- index.php                 Entry point (redirects to login or dashboard)
|-- config/
|   |-- config.php            Settings: debug mode, base URL, database login
|   `-- database.php          Creates the PDO connection ($pdo)
|-- includes/
|   |-- functions.php         Session start, e(), redirect(), CSRF, flash messages, input helpers
|   |-- auth_check.php        Login protection for every private page
|   |-- header.php, navbar.php, footer.php     Shared layout
|   |-- category_functions.php, transaction_functions.php, budget_functions.php
|   |-- transaction_form.php, budget_form.php, report_tabs.php
|-- auth/                     register, login, logout, profile
|-- dashboard/index.php
|-- categories/               index, add, edit, delete
|-- transactions/             index (search, filter, paging), add, edit, delete
|-- budgets/                  index (comparison), add, edit, delete
|-- reports/                  monthly.php, category.php
|-- assets/css/style.css, assets/js/script.js, charts.js, chart.umd.js
|-- database/                 database.sql, demo_data.sql, remove_demo_data.sql
`-- docs/                     TEST_CASES.md, PROJECT_NOTES.md, DEMO_AND_VIVA_GUIDE.md
```

## Security features

- Passwords stored with `password_hash()` and checked with `password_verify()`
- PDO prepared statements for every query (protection against SQL injection)
- PHP sessions, `session_regenerate_id()` after login, protected pages check the session
- Every user-specific query includes `user_id`, so one user can never see or change another user's data
- IDs from the URL are never trusted: records are loaded only if they belong to the logged-in user
- State-changing actions (add, edit, delete, logout) use POST with a CSRF token
- All user text is escaped with `htmlspecialchars()` (protection against XSS)
- Server-side validation of every form
- `.htaccess` files block direct browser access to `config/`, `includes/`, `database/` and `docs/`

## Debug mode

`config/config.php` contains `APP_DEBUG`.

- `false` (default): visitors see only friendly messages. Real errors are written to the Apache error log
  (`C:\xampp\apache\logs\error.log`). Use this for the demo.
- `true`: detailed errors are shown on the page. Use this while fixing a problem, then set it back to `false`.

## Troubleshooting

| Problem | Fix |
|---|---|
| Page not found (404) | The folder must be exactly `C:\xampp\htdocs\PEMS`. Check the port in the address |
| Blank white page | Set `APP_DEBUG` to `true` in `config/config.php`, reload, read the error |
| "Unable to connect to the database" | Start MySQL in XAMPP; check `DB_USER` / `DB_PASS` in `config/config.php`; make sure `database.sql` was imported |
| "could not find driver" | In `php.ini` remove the `;` before `extension=pdo_mysql`, restart Apache |
| `mb_strlen()` undefined | In `php.ini` remove the `;` before `extension=mbstring`, restart Apache |
| Page looks unstyled | Press Ctrl+F5. Check `BASE_URL` in `config/config.php` matches the folder name |
| Charts do not appear | Check that `assets/js/chart.umd.js` exists; press Ctrl+F5 |
| "Invalid request. Please try again." | The session expired. Reload the page and submit again |
| MySQL does not start | Another program uses port 3306. Close it or change the MySQL port in XAMPP |

## Documentation

- `docs/TEST_CASES.md`: test case table for your testing chapter
- `docs/PROJECT_NOTES.md`: notes for writing the BCA project documentation
- `docs/DEMO_AND_VIVA_GUIDE.md`: demo order and viva questions about the code
