-- =====================================================================
-- demo_data.sql  -  OPTIONAL DEMO DATA for PEMS (not needed for the system to work)
--
-- Creates ONE demo user with categories, about 4 months of sample transactions
-- and sample budgets for the current month. Dates are relative to today.
--
--   Demo login:  demo@pems.test   /   Demo@1234
--
-- Import this file ONCE (phpMyAdmin -> pems_db -> Import), after database.sql.
-- To remove the demo user later, import database/remove_demo_data.sql.
-- =====================================================================

USE pems_db;

INSERT INTO users (full_name, email, password_hash)
VALUES ('Demo User', 'demo@pems.test', '$2y$10$jr.OGhMFvtwtnq8II8LtQewhpwV3Hb1/3xeURLIcJwliH6zZ06Vqe');
SET @uid = LAST_INSERT_ID();

INSERT INTO categories (user_id, category_name, category_type) VALUES
(@uid, 'Food', 'expense'),
(@uid, 'Transport', 'expense'),
(@uid, 'Education', 'expense'),
(@uid, 'Bills', 'expense'),
(@uid, 'Shopping', 'expense'),
(@uid, 'Health', 'expense'),
(@uid, 'Entertainment', 'expense'),
(@uid, 'Other', 'expense'),
(@uid, 'Salary', 'income'),
(@uid, 'Other Income', 'income');

-- Sample transactions (DEMO DATA)
INSERT INTO transactions (user_id, category_id, amount, type, description, transaction_date)
SELECT @uid, c.category_id, v.amount, v.type, v.description, v.d FROM categories c JOIN (
  SELECT 'Salary' AS cname, 'income' AS type, 45000.00 AS amount, 'Monthly salary' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-01'), INTERVAL 0 DAY) AS d
  UNION ALL
  SELECT 'Bills' AS cname, 'expense' AS type, 3500.00 AS amount, 'Electricity and internet' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-01'), INTERVAL 2 DAY) AS d
  UNION ALL
  SELECT 'Food' AS cname, 'expense' AS type, 1850.00 AS amount, 'Groceries' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-01'), INTERVAL 3 DAY) AS d
  UNION ALL
  SELECT 'Transport' AS cname, 'expense' AS type, 600.00 AS amount, 'Bus and fuel' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-01'), INTERVAL 6 DAY) AS d
  UNION ALL
  SELECT 'Food' AS cname, 'expense' AS type, 950.00 AS amount, 'Restaurant' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-01'), INTERVAL 8 DAY) AS d
  UNION ALL
  SELECT 'Education' AS cname, 'expense' AS type, 2500.00 AS amount, 'Books and course fees' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-01'), INTERVAL 11 DAY) AS d
  UNION ALL
  SELECT 'Shopping' AS cname, 'expense' AS type, 2200.00 AS amount, 'Clothes' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-01'), INTERVAL 14 DAY) AS d
  UNION ALL
  SELECT 'Entertainment' AS cname, 'expense' AS type, 700.00 AS amount, 'Movie and games' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-01'), INTERVAL 17 DAY) AS d
  UNION ALL
  SELECT 'Food' AS cname, 'expense' AS type, 1400.00 AS amount, 'Groceries' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-01'), INTERVAL 20 DAY) AS d
  UNION ALL
  SELECT 'Health' AS cname, 'expense' AS type, 500.00 AS amount, 'Medicine' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 3 MONTH), '%Y-%m-01'), INTERVAL 23 DAY) AS d
  UNION ALL
  SELECT 'Salary' AS cname, 'income' AS type, 45000.00 AS amount, 'Monthly salary' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-01'), INTERVAL 0 DAY) AS d
  UNION ALL
  SELECT 'Bills' AS cname, 'expense' AS type, 3500.00 AS amount, 'Electricity and internet' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-01'), INTERVAL 2 DAY) AS d
  UNION ALL
  SELECT 'Food' AS cname, 'expense' AS type, 1850.00 AS amount, 'Groceries' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-01'), INTERVAL 3 DAY) AS d
  UNION ALL
  SELECT 'Transport' AS cname, 'expense' AS type, 600.00 AS amount, 'Bus and fuel' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-01'), INTERVAL 6 DAY) AS d
  UNION ALL
  SELECT 'Food' AS cname, 'expense' AS type, 950.00 AS amount, 'Restaurant' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-01'), INTERVAL 8 DAY) AS d
  UNION ALL
  SELECT 'Education' AS cname, 'expense' AS type, 800.00 AS amount, 'Books and course fees' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-01'), INTERVAL 11 DAY) AS d
  UNION ALL
  SELECT 'Shopping' AS cname, 'expense' AS type, 4800.00 AS amount, 'Clothes' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-01'), INTERVAL 14 DAY) AS d
  UNION ALL
  SELECT 'Entertainment' AS cname, 'expense' AS type, 700.00 AS amount, 'Movie and games' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-01'), INTERVAL 17 DAY) AS d
  UNION ALL
  SELECT 'Food' AS cname, 'expense' AS type, 1400.00 AS amount, 'Groceries' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-01'), INTERVAL 20 DAY) AS d
  UNION ALL
  SELECT 'Health' AS cname, 'expense' AS type, 500.00 AS amount, 'Medicine' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-01'), INTERVAL 23 DAY) AS d
  UNION ALL
  SELECT 'Salary' AS cname, 'income' AS type, 45000.00 AS amount, 'Monthly salary' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01'), INTERVAL 0 DAY) AS d
  UNION ALL
  SELECT 'Bills' AS cname, 'expense' AS type, 3800.00 AS amount, 'Electricity and internet' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01'), INTERVAL 2 DAY) AS d
  UNION ALL
  SELECT 'Food' AS cname, 'expense' AS type, 1850.00 AS amount, 'Groceries' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01'), INTERVAL 3 DAY) AS d
  UNION ALL
  SELECT 'Transport' AS cname, 'expense' AS type, 600.00 AS amount, 'Bus and fuel' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01'), INTERVAL 6 DAY) AS d
  UNION ALL
  SELECT 'Food' AS cname, 'expense' AS type, 950.00 AS amount, 'Restaurant' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01'), INTERVAL 8 DAY) AS d
  UNION ALL
  SELECT 'Education' AS cname, 'expense' AS type, 2500.00 AS amount, 'Books and course fees' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01'), INTERVAL 11 DAY) AS d
  UNION ALL
  SELECT 'Shopping' AS cname, 'expense' AS type, 2200.00 AS amount, 'Clothes' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01'), INTERVAL 14 DAY) AS d
  UNION ALL
  SELECT 'Entertainment' AS cname, 'expense' AS type, 700.00 AS amount, 'Movie and games' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01'), INTERVAL 17 DAY) AS d
  UNION ALL
  SELECT 'Food' AS cname, 'expense' AS type, 1400.00 AS amount, 'Groceries' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01'), INTERVAL 20 DAY) AS d
  UNION ALL
  SELECT 'Health' AS cname, 'expense' AS type, 500.00 AS amount, 'Medicine' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01'), INTERVAL 23 DAY) AS d
  UNION ALL
  SELECT 'Salary' AS cname, 'income' AS type, 45000.00 AS amount, 'Monthly salary' AS description, DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL LEAST(0, DAY(CURDATE()) - 1) DAY) AS d
  UNION ALL
  SELECT 'Bills' AS cname, 'expense' AS type, 3500.00 AS amount, 'Electricity and internet' AS description, DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL LEAST(2, DAY(CURDATE()) - 1) DAY) AS d
  UNION ALL
  SELECT 'Food' AS cname, 'expense' AS type, 1850.00 AS amount, 'Groceries' AS description, DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL LEAST(3, DAY(CURDATE()) - 1) DAY) AS d
  UNION ALL
  SELECT 'Transport' AS cname, 'expense' AS type, 600.00 AS amount, 'Bus and fuel' AS description, DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL LEAST(6, DAY(CURDATE()) - 1) DAY) AS d
  UNION ALL
  SELECT 'Food' AS cname, 'expense' AS type, 950.00 AS amount, 'Restaurant' AS description, DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL LEAST(8, DAY(CURDATE()) - 1) DAY) AS d
  UNION ALL
  SELECT 'Education' AS cname, 'expense' AS type, 800.00 AS amount, 'Books and course fees' AS description, DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL LEAST(11, DAY(CURDATE()) - 1) DAY) AS d
  UNION ALL
  SELECT 'Shopping' AS cname, 'expense' AS type, 2200.00 AS amount, 'Clothes' AS description, DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL LEAST(14, DAY(CURDATE()) - 1) DAY) AS d
  UNION ALL
  SELECT 'Entertainment' AS cname, 'expense' AS type, 700.00 AS amount, 'Movie and games' AS description, DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL LEAST(17, DAY(CURDATE()) - 1) DAY) AS d
  UNION ALL
  SELECT 'Food' AS cname, 'expense' AS type, 1400.00 AS amount, 'Groceries' AS description, DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL LEAST(20, DAY(CURDATE()) - 1) DAY) AS d
  UNION ALL
  SELECT 'Health' AS cname, 'expense' AS type, 500.00 AS amount, 'Medicine' AS description, DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL LEAST(23, DAY(CURDATE()) - 1) DAY) AS d
  UNION ALL
  SELECT 'Other Income' AS cname, 'income' AS type, 3000.00 AS amount, 'Freelance work' AS description, DATE_ADD(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-01'), INTERVAL 15 DAY) AS d
  UNION ALL
  SELECT 'Other Income' AS cname, 'income' AS type, 1500.00 AS amount, 'Gift received' AS description, DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL LEAST(1, DAY(CURDATE()) - 1) DAY) AS d
) v ON v.cname = c.category_name AND v.type = c.category_type
WHERE c.user_id = @uid;

-- Sample budgets for the current month (DEMO DATA)
INSERT INTO budgets (user_id, category_id, budget_amount, `month`, `year`)
SELECT @uid, c.category_id, b.amount, MONTH(CURDATE()), YEAR(CURDATE())
FROM categories c JOIN (
  SELECT 'Food' AS cname, 4000.00 AS amount
  UNION ALL SELECT 'Transport', 1000.00
  UNION ALL SELECT 'Entertainment', 500.00
  UNION ALL SELECT 'Shopping', 3000.00
) b ON b.cname = c.category_name
WHERE c.user_id = @uid AND c.category_type = 'expense';
