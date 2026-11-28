# PEMS: Notes for the BCA Project Documentation

Use these notes when writing your project report. Check every statement against your own project and proposal before copying it.

## 1. Proposal item to implementation

| Proposal item | Where it is implemented |
|---|---|
| User Management (register, login, logout, account information) | `auth/register.php`, `login.php`, `logout.php`, `profile.php` |
| Income Management | `transactions/` (type = income) |
| Expense Management (add, update, delete, search, filter, view) | `transactions/` (type = expense), filters in `transactions/index.php` |
| Category Management | `categories/` |
| Dashboard (total income, total expenses, balance, recent transactions) | `dashboard/index.php` |
| Reports and Analytics (monthly and category-wise summaries, graphs) | `reports/monthly.php`, `reports/category.php`, `assets/js/charts.js` |
| Budget Monitoring (monthly budget compared with spending) | `budgets/` |
| Database: users, categories, transactions, budgets | `database/database.sql` |
| Balance = Total Income - Total Expense | SQL in `dashboard/index.php` |
| Iterative development, module by module | Built in phases 1 to 12 |

## 2. Things added beyond the literal proposal text (mention these honestly)

| Addition | Reason |
|---|---|
| Chart.js library (local file, MIT license) | The proposal asks for graphical representations but names no method. Chart.js is easy to use and change |
| Two default income categories, "Salary" and "Other Income" | Every transaction needs a category, and the proposal's example categories are expense categories |
| CSRF token on every form | Protects forms against requests from other websites |
| Paging (15 rows per page) on the transaction list | Keeps long lists fast |
| "No future dates" rule for transactions | Keeps records realistic. Can be removed in `validate_transaction_input()` |
| Budgets only for expense categories | A budget limits spending, so it makes no sense for income |
| `.htaccess` files and `database/` folder | Stops visitors from downloading code or SQL files |

## 3. Database design

| Table | Key columns | Notes |
|---|---|---|
| users | user_id (PK), full_name, email (UNIQUE), password_hash, status, created_at | Password is stored as a hash |
| categories | category_id (PK), user_id (FK), category_name, category_type, status | UNIQUE (user_id, category_name, category_type). Each category belongs to one user |
| transactions | transaction_id (PK), user_id (FK), category_id (FK), amount DECIMAL(12,2), type, description, transaction_date, created_at | CHECK amount > 0. Indexes on (user_id, transaction_date) and (user_id, type) |
| budgets | budget_id (PK), user_id (FK), category_id (FK), budget_amount DECIMAL(12,2), month, year | UNIQUE (user_id, category_id, month, year). CHECK amount > 0 and month 1 to 12 |

Relationships (ER diagram): one user has many categories, many transactions and many budgets; one category has many transactions and many budgets.
A category that has transactions or budgets cannot be deleted (ON DELETE RESTRICT), so a used category never disappears silently. For the same reason a user cannot be removed with one DELETE: their transactions, budgets and categories must be deleted first, in that order (see `database/remove_demo_data.sql`). The application itself has no "delete account" feature.
`DECIMAL` is used for money so values are exact (floating-point numbers can give results like 0.30000000000000004).

## 4. Architecture and request flow

Every page follows the same pattern:

1. `includes/auth_check.php`: start the session, require login, give the page the `$pdo` connection
2. Read and validate the input (`read_id()`, `post_text()`, validation functions)
3. Run prepared SQL queries that always include `user_id`
4. `includes/header.php` to print the page layout, then the HTML, then `includes/footer.php`

Forms that change data: the browser sends a POST request with a CSRF token, the server validates it, saves the data, sets a flash message and redirects (Post/Redirect/Get), so refreshing does not submit the form twice.

## 5. Important formulas and queries (for the system design chapter)

- Balance = Total Income - Total Expenses, calculated in SQL with `SUM(CASE WHEN type = 'income' THEN amount END)`.
- Month totals use `transaction_date BETWEEN first_day AND last_day`, which can use the index.
- Monthly report: `GROUP BY MONTH(transaction_date)` for the selected year.
- Category report: `JOIN categories ... WHERE type = 'expense' GROUP BY category`, share % = category total / grand total x 100.
- Budget: Spent = SUM of the user's expense transactions in that category during that month; Remaining = Budget - Spent; over budget when Spent > Budget.
- Filters: conditions are added to the WHERE clause only when a filter is used; values are always bound as parameters. The description search uses `LIKE` with `%` and `_` escaped.

## 6. Security summary

Prepared statements (SQL injection), `htmlspecialchars()` (XSS), `password_hash` and `password_verify`, session ID regeneration after login, CSRF tokens, POST for every change, ownership checks (`WHERE ... AND user_id = ?`) for every record, server-side validation, friendly error messages with the real error in the log, and blocked direct access to internal folders.

## 7. Testing

See `docs/TEST_CASES.md` (about 100 test cases in 10 groups). Run them yourself and fill in the Actual Result and Status columns for your testing chapter.

## 8. Known limitations (also good for the proposal's limitations section)

- Single currency (Rs.), single user per account, no sharing between users
- No password reset by email and no email verification
- No export (PDF or CSV) and no recurring transactions
- Amounts are entered manually (no bank connection, as stated in the proposal's scope)
- A transaction's date cannot be in the future
- Tested on a small amount of data; not designed for very large databases

## 9. Optional suggestions, not part of the approved scope (not implemented)

- Export reports to CSV or PDF
- Recurring transactions
- Budget warning at 80% usage
- Password reset
- Dark mode

Only add these if your supervisor approves them.

## 10. Screenshot checklist for the report

1. Registration page (with a validation error)
2. Login page
3. Dashboard with data
4. Dashboard of a new user (empty state)
5. Transactions list
6. Filter in use (type, category, dates)
7. Add transaction form
8. Categories list
9. Budgets page with one over-budget row
10. Monthly summary report with the bar chart
11. Category-wise report with the doughnut chart
12. My Profile page
13. phpMyAdmin: the four tables
14. phpMyAdmin Designer view showing the relationships
15. phpMyAdmin: `users` table showing hashed passwords
16. The same page on a narrow browser window (responsive layout)
