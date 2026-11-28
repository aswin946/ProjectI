# Demo and Viva Guide

## 1. Before the demo (checklist)

- Start Apache and MySQL in XAMPP and open the system once to check that it works
- Import `database/demo_data.sql` (or register a user and add some data yourself)
- Check `APP_DEBUG` is `false` in `config/config.php`
- Open the pages you will show in separate tabs; keep phpMyAdmin open on `pems_db`
- Log in as the demo user in one browser, and prepare a second user in another browser for the isolation demo
- Charge the laptop and check the date and time zone are correct

## 2. Suggested 7-minute demo order

1. **Register** a new user (30 seconds). Show phpMyAdmin: the password is a hash, 10 default categories were created.
2. **Add** an income and two expenses. Show the type changing the category list.
3. **Dashboard:** totals, balance, this month, recent transactions. Say: "Balance = Total Income - Total Expenses, calculated by SQL."
4. **Transactions:** use two filters and the text search; show paging with the demo data.
5. **Categories:** add one, show that a used category cannot be deleted.
6. **Budgets:** show one budget within limit and one over budget. Add an expense and refresh to show the numbers change.
7. **Reports:** monthly bar chart and category doughnut chart.
8. **Security:** in the second browser, log in as another user and show that the data is separate. Try changing the `id` in an edit URL: "Transaction not found."

## 3. Questions about the code (with short answers)

**Why PDO and prepared statements?**
PDO is PHP's database interface. A prepared statement sends the SQL and the values separately, so a value can never change the meaning of the SQL. This prevents SQL injection.

**How does login work?**
The login page finds the user by email and checks the typed password against the stored hash with `password_verify()`. If it matches, the user ID is saved in `$_SESSION` and the session ID is regenerated. Every private page includes `auth_check.php`, which redirects to login when there is no session.

**Why not store passwords normally?**
If the database is leaked, plain passwords are exposed. `password_hash()` creates a salted one-way hash, so the original password cannot be recovered.

**How do you stop one user from seeing another user's data?**
Every query that reads or changes user data has `user_id = ?` with the ID from the session. For edit and delete pages the record is first loaded with both its own ID and the user ID; if it does not belong to the user, the page says "not found".

**What is CSRF and how do you prevent it?**
A malicious website could make your browser submit a form to our site while you are logged in. Every form contains a secret random token stored in the session; the server rejects the request if the token is missing or wrong.

**What is XSS and how do you prevent it?**
XSS means a user's text containing HTML or JavaScript runs in another page. We print all user text with `htmlspecialchars()` (function `e()`), so it is shown as plain text.

**Why DECIMAL for money?**
`DECIMAL` stores exact decimal values. `FLOAT` can give small errors.

**How is the balance calculated?**
One SQL query sums income rows and expense rows with `SUM(CASE WHEN ...)` and subtracts them. The database does the work; PHP only prints the result.

**How does budget monitoring work?**
A budget stores the amount for one expense category in one month. The budget page adds up the user's expense transactions of that category in that month (`Spent`), then shows `Remaining = Budget - Spent` and marks it over budget when Spent is larger.

**How does filtering work?**
The filter form sends values in the URL (GET). PHP checks them, adds a condition to the WHERE clause for each filter that is used, and binds the values as parameters. MySQL returns only the matching rows. JavaScript is not used for filtering.

**How does paging work?**
A COUNT query gives the number of matching rows, the page number is limited to a valid range, and the list query uses `LIMIT 15 OFFSET (page - 1) x 15`.

**How are the charts drawn?**
PHP puts the report numbers into the `<canvas>` tag as `data-` attributes in JSON format. `assets/js/charts.js` reads them and gives them to Chart.js, which draws the bar and doughnut charts.

**What is Post/Redirect/Get?**
After a form is saved, the server redirects to another page instead of showing a page directly, so pressing refresh does not submit the form again.

**Why is there a UNIQUE key on budgets?**
So the same category cannot have two budgets for the same month. The database enforces this even if the PHP check is bypassed.

**What happens when you delete a user?**
Foreign keys with `ON DELETE CASCADE` remove that user's categories, transactions and budgets.

**What would you improve with more time?**
Only if approved: export to CSV/PDF, recurring transactions, password reset. The current project follows the proposal's scope.

## 4. If something goes wrong during the demo

- **Blank page:** set `APP_DEBUG` to `true`, press F5, read the error. Common causes are MySQL not running or a wrong database password.
- **Page looks unstyled:** press Ctrl+F5.
- **Charts missing:** press Ctrl+F5; check that the browser is not blocking scripts.
- **Wrong date or "This month" looks empty:** the demo data is relative to the day it was imported. Check the computer date, or add a few new transactions live.
