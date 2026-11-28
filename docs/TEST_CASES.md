# PEMS Test Cases

Fill in **Actual Result** and **Status** while you run each test on your own computer. These are the tests used during development; run them yourself before the demo and note any differences.
Use two accounts (User A and User B) for the cross-user tests.

## 1. Registration and Login

| ID | Test case | Input | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| AUTH-01 | Register with valid details | Name, new email, password of 8+ chars, same confirm password | Redirected to login with success message; user row created; 10 default categories created | | |
| AUTH-02 | Register with an existing email | Same email again (also try different upper/lower case) | "This email is already registered." | | |
| AUTH-03 | Register with short password | Password `abc` | Error: password must be 8 to 72 characters | | |
| AUTH-04 | Register with different confirm password | Password and confirm differ | Error: passwords do not match | | |
| AUTH-05 | Register with invalid email | `abc` | Error: valid email required | | |
| AUTH-06 | Password stored securely | Check `users.password_hash` in phpMyAdmin | Starts with `$2y$`, not the real password | | |
| AUTH-07 | Login with correct details | Registered email and password | Dashboard opens | | |
| AUTH-08 | Login with wrong password | Wrong password | "Invalid login credentials." | | |
| AUTH-09 | Login with unknown email | Unregistered email | "Invalid login credentials." (same message) | | |
| AUTH-10 | Logout | Click Logout | Login page with "You have been logged out." | | |
| AUTH-11 | Back button after logout | Press Back | Protected page is not shown; login page appears | | |
| AUTH-12 | Open protected page without login | Open `/dashboard/index.php` while logged out | Redirected to login | | |
| AUTH-13 | Open login page while logged in | Open `/auth/login.php` | Redirected to dashboard | | |
| AUTH-14 | Change name | New name in My Profile | Name updated in navbar | | |
| AUTH-15 | Change password with wrong current password | Wrong current password | Error; password unchanged | | |
| AUTH-16 | Change password correctly | Correct current + new password | Success; login works with the new password only | | |

## 2. Categories

| ID | Test case | Input | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| CAT-01 | Default categories | New user opens Categories | 8 expense + 2 income categories | | |
| CAT-02 | Add category | Name `Gym`, type Expense | Added with success message | | |
| CAT-03 | Duplicate category | `gym` as Expense again | "You already have an expense category with this name." | | |
| CAT-04 | Same name, other type | `Gym` as Income | Allowed | | |
| CAT-05 | Empty / too long name | Empty; 51 characters | Validation error | | |
| CAT-06 | HTML in name | `<b>Hi</b>` | Displayed as text, not formatted | | |
| CAT-07 | Edit category | New name, status Inactive | Updated; shows Inactive badge | | |
| CAT-08 | Delete unused category | Delete, press OK | Removed | | |
| CAT-09 | Cancel delete | Delete, press Cancel | Nothing deleted | | |
| CAT-10 | Delete category used by a transaction | Delete Food after adding a Food transaction | Delete button is not shown; direct request refused with message | | |
| CAT-11 | Change type of used category | Edit a used category, try changing type | Type field disabled; type unchanged | | |
| CAT-12 | Cross-user edit | User B opens `edit.php?id=` of A's category | "Category not found." | | |
| CAT-13 | Invalid ID | `edit.php?id=abc` and `id=99999` | "Category not found." | | |

## 3. Transactions

| ID | Test case | Input | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| TXN-01 | Add income | Salary, 50000.50, today | Saved; shown in green with + sign | | |
| TXN-02 | Add expense | Food, 1200, today | Saved; shown in red with - sign | | |
| TXN-03 | Category list follows type | Switch type between Income and Expense | Category list changes | | |
| TXN-04 | Invalid amount | 0, -5, 12.345, text | Error message; nothing saved | | |
| TXN-05 | Future / invalid date | Date after today; 2026-02-30 | Error message | | |
| TXN-06 | Category does not match type | Income with Food (use browser tools or edit the form) | "The selected category is not an income category." | | |
| TXN-07 | Other user's category | Send another user's `category_id` | "Please select a valid category." | | |
| TXN-08 | Edit transaction | Change amount and description | Updated in list | | |
| TXN-09 | Delete transaction | Delete, press OK | Removed; dashboard totals change | | |
| TXN-10 | Cross-user open / edit / delete | B uses A's transaction ID | "Transaction not found."; A's data unchanged | | |
| TXN-11 | Description with HTML | `<script>alert(1)</script>` | Shown as plain text; no popup | | |
| TXN-12 | Inactive category | Make Food inactive, add expense | Food not in list; old Food transactions still editable | | |

## 4. Search and Filter

| ID | Test case | Input | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| FLT-01 | Filter by type | Type = Income | Only income rows | | |
| FLT-02 | Filter by category | Category = Food | Only Food rows | | |
| FLT-03 | Filter by date range | From/To dates | Only rows inside the range (inclusive) | | |
| FLT-04 | Search description | `lunch` | Only rows whose description contains it | | |
| FLT-05 | Combined filters | Type + category + dates + text | Rows matching all conditions; totals match the filtered rows | | |
| FLT-06 | No match | Search `zzzz` | "No transactions match your filters." | | |
| FLT-07 | Invalid dates | From date after To date | Error message; date filter ignored | | |
| FLT-08 | Special characters in search | `%`, `_`, `' OR 1=1 --` | Searched as plain text; no error; no extra rows | | |
| FLT-09 | Paging | 16+ transactions | 15 per page; Next/Previous keep the filters | | |
| FLT-10 | Clear filters | Click Clear | All transactions again | | |

## 5. Dashboard

| ID | Test case | Input | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| DSH-01 | New user | Open dashboard | Rs. 0.00 everywhere, empty-state message | | |
| DSH-02 | Totals | Income 50000, expense 1200 | Income 50,000.00; Expenses 1,200.00; Balance 48,800.00 | | |
| DSH-03 | Negative balance | Expense bigger than income | Balance in red with note | | |
| DSH-04 | This month card | Add a transaction dated last month | Totals change, "This month" does not | | |
| DSH-05 | Recent transactions | 6+ transactions | Only newest 5; "View all" link | | |
| DSH-06 | Totals update | Edit/delete a transaction | Dashboard updates | | |
| DSH-07 | Data isolation | Log in as B | B sees only B's numbers | | |

## 6. Reports and Charts

| ID | Test case | Input | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| RPT-01 | Monthly summary | Open Reports > Monthly Summary | 12 months with income, expenses, balance; totals row matches the dashboard for that year | | |
| RPT-02 | Monthly chart | Same page | Bar chart with income and expenses per month | | |
| RPT-03 | Year with no data | New user | "No transactions were recorded" message | | |
| RPT-04 | Category report | Choose a month | Expense categories with total, share % and count; total matches the monthly report | | |
| RPT-05 | Category chart | Same page | Doughnut chart; income is not included | | |
| RPT-06 | Whole year | Month = Whole year | Totals for the entire year | | |
| RPT-07 | Period with no expenses | Choose an empty month | "No expenses were recorded" message | | |
| RPT-08 | Report isolation | Log in as B | Reports show only B's data | | |

## 7. Budgets

| ID | Test case | Input | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| BUD-01 | Set budget | Food, this month, 1500 | Saved; shown in the list | | |
| BUD-02 | Duplicate budget | Same category and month again | "A budget for this category and month already exists." | | |
| BUD-03 | Income category | Choose an income category (forced request) | "A budget can only be set for an expense category." | | |
| BUD-04 | Invalid amount | 0, -1, 5.555, text | Error message | | |
| BUD-05 | Spent calculation | Add Food expenses this month | Spent equals the total of that category's expenses this month; Remaining = Budget - Spent | | |
| BUD-06 | Over budget | Spend more than the budget | Red bar, "Over budget by Rs. ..." | | |
| BUD-07 | Only the month counts | Add Food expense in another month | Spent for this month does not change | | |
| BUD-08 | Income not counted | Add income in a budgeted category month | Spent does not change | | |
| BUD-09 | Edit budget | Change amount | Comparison updates | | |
| BUD-10 | Delete budget | Delete, press OK | Removed | | |
| BUD-11 | Category with budget | Try deleting the category | Not allowed | | |
| BUD-12 | Cross-user | B opens/edits/deletes A's budget | "Budget not found."; A's data unchanged | | |
| BUD-13 | Month and year selector | Choose another month | Shows that month's budgets | | |

## 8. Security and Validation

| ID | Test case | Input | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| SEC-01 | SQL injection in login | Email `' OR 1=1 --` | Login fails; no error | | |
| SEC-02 | Delete by URL (GET) | Open `/transactions/delete.php?transaction_id=1` | Nothing is deleted | | |
| SEC-03 | Form without CSRF token | Submit a form after removing the hidden token (browser tools) | "Invalid request. Please try again." | | |
| SEC-04 | Direct access to config | Open `/PEMS/config/database.php` and `/PEMS/database/database.sql` in the browser | Access denied (403) | | |
| SEC-05 | Wrong input types | Open `/transactions/index.php?type[]=x` | Page loads normally; no error | | |
| SEC-06 | Session after logout | Copy a page URL, logout, open it | Redirected to login | | |

## 9. Database

| ID | Test case | Input | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| DB-01 | Tables exist | phpMyAdmin > pems_db | users, categories, transactions, budgets | | |
| DB-02 | Foreign keys | phpMyAdmin > Designer | 5 relationships | | |
| DB-03 | Amount precision | Add 0.10 and 0.20 | Dashboard shows exactly 0.30 | | |
| DB-04 | Unique email | Insert a duplicate email in phpMyAdmin | Database refuses it | | |
| DB-05 | Check constraint | Insert a transaction with amount 0 in phpMyAdmin | Database refuses it | | |
| DB-06 | Category RESTRICT rule | In phpMyAdmin delete a category that has transactions | Database refuses (foreign key error) | | |
| DB-07 | Remove demo data | Import `database/remove_demo_data.sql` | Demo user and all its rows are removed without errors | | |

## 10. Multi-user

| ID | Test case | Input | Expected result | Actual result | Status |
|---|---|---|---|---|---|
| MU-01 | Two browsers | A in Chrome, B in Edge, add data in both | Neither sees the other's data in any page (dashboard, transactions, categories, budgets, reports) | | |
| MU-02 | Two registrations | Two users with the same category names | Both can have a "Food" category independently | | |
