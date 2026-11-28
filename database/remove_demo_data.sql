-- =====================================================================
-- remove_demo_data.sql  -  removes the OPTIONAL demo user and all its data
-- Run in phpMyAdmin (pems_db -> SQL tab or Import).
--
-- The order matters: transactions and budgets first, then categories, then the user.
-- (A category that is still used cannot be deleted, so the data using it goes first.)
-- =====================================================================

USE pems_db;

SET @uid = (SELECT user_id FROM users WHERE email = 'demo@pems.test');

DELETE FROM transactions WHERE user_id = @uid;
DELETE FROM budgets      WHERE user_id = @uid;
DELETE FROM categories   WHERE user_id = @uid;
DELETE FROM users        WHERE user_id = @uid;
