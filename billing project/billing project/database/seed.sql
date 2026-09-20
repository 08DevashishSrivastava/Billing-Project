-- FinPilot Seed Data
-- Demo user: demo@finpilot.app / finpilot123
-- Run AFTER schema.sql

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE statement_imports;
TRUNCATE recurring_obligations;
TRUNCATE goals;
TRUNCATE budgets;
TRUNCATE transactions;
TRUNCATE categories;
TRUNCATE accounts;
TRUNCATE users;
SET FOREIGN_KEY_CHECKS = 1;

-- ── Demo User (password: finpilot123) ─────────────────────────────────────────
INSERT INTO users (id, name, email, password, currency, last_login, created_at) VALUES
(1, 'Alex Sharma', 'demo@finpilot.app', '$2y$12$E70ICOEtyhVkCtA.lCG4je3tPjoIqE.YZkieSclXVieATqJr9HGHC', '₹', NOW(), '2026-01-01 00:00:00');

-- ── System Default Categories ─────────────────────────────────────────────────
INSERT INTO categories (id, user_id, name, type, icon, color, is_default) VALUES
(1,  NULL, 'Salary',          'income',  'bi-briefcase',        '#10b981', 1),
(2,  NULL, 'Freelance',       'income',  'bi-laptop',           '#06b6d4', 1),
(3,  NULL, 'Investment',      'income',  'bi-graph-up-arrow',   '#8b5cf6', 1),
(4,  NULL, 'Groceries',       'expense', 'bi-cart3',            '#f59e0b', 1),
(5,  NULL, 'Dining Out',      'expense', 'bi-cup-hot',          '#ef4444', 1),
(6,  NULL, 'Transportation',  'expense', 'bi-car-front',        '#3b82f6', 1),
(7,  NULL, 'Entertainment',   'expense', 'bi-film',             '#ec4899', 1),
(8,  NULL, 'Utilities',       'expense', 'bi-lightning-charge', '#f97316', 1),
(9,  NULL, 'Subscriptions',   'expense', 'bi-collection-play',  '#a855f7', 1),
(10, NULL, 'Healthcare',      'expense', 'bi-heart-pulse',      '#14b8a6', 1),
(11, NULL, 'Shopping',        'expense', 'bi-bag',              '#f43f5e', 1),
(12, NULL, 'Education',       'expense', 'bi-book',             '#0ea5e9', 1),
(13, NULL, 'Rent/Mortgage',   'expense', 'bi-house',            '#6366f1', 1),
(14, NULL, 'Insurance',       'expense', 'bi-shield-check',     '#64748b', 1),
(15, NULL, 'Miscellaneous',   'both',    'bi-three-dots',       '#94a3b8', 1);

-- ── Demo Accounts ─────────────────────────────────────────────────────────────
INSERT INTO accounts (id, user_id, name, type, balance, institution, color, is_active) VALUES
(1, 1, 'HDFC Salary Account',   'checking',    85000.00, 'HDFC Bank',  '#10b981', 1),
(2, 1, 'SBI Savings',           'savings',    120000.00, 'SBI',        '#6366f1', 1),
(3, 1, 'ICICI Credit Card',     'credit_card',  -8500.00, 'ICICI Bank', '#ef4444', 1),
(4, 1, 'Cash Wallet',           'cash',          2500.00, NULL,         '#f59e0b', 1);

-- ── Demo Transactions (last 3 months) ─────────────────────────────────────────
INSERT INTO transactions (user_id, account_id, category_id, transaction_date, amount, type, description, merchant, source) VALUES
-- September 2026
(1,1,1,'2026-09-01',85000.00,'income','Monthly Salary','HDFC Payroll','manual'),
(1,1,4,'2026-09-02',3200.00,'expense','Weekly groceries','BigBazaar','manual'),
(1,3,5,'2026-09-03',1800.00,'expense','Dinner with family','Barbeque Nation','manual'),
(1,3,9,'2026-09-04',649.00,'expense','Netflix subscription','Netflix','manual'),
(1,3,9,'2026-09-04',199.00,'expense','Spotify Premium','Spotify','manual'),
(1,1,6,'2026-09-05',450.00,'expense','Uber rides','Uber','manual'),
(1,1,13,'2026-09-06',18000.00,'expense','Monthly rent','Landlord','manual'),
(1,3,11,'2026-09-08',4200.00,'expense','Amazon shopping','Amazon','manual'),
(1,1,8,'2026-09-10',1200.00,'expense','Electricity bill','BESCOM','manual'),
(1,1,4,'2026-09-12',2800.00,'expense','Grocery run','Reliance Fresh','manual'),
(1,1,2,'2026-09-15',25000.00,'income','Freelance project payment','Client ABC','manual'),
(1,3,5,'2026-09-16',950.00,'expense','Lunch out','Cafe Coffee Day','manual'),
(1,1,12,'2026-09-18',5000.00,'expense','Online course','Udemy','manual'),
(1,1,10,'2026-09-20',1500.00,'expense','Doctor visit','Apollo Clinic','manual'),
(1,3,7,'2026-09-22',1200.00,'expense','Movie tickets','PVR Cinemas','manual'),
(1,1,4,'2026-09-24',3100.00,'expense','Supermarket','DMart','manual'),
(1,1,6,'2026-09-26',600.00,'expense','Metro card recharge','BMTC','manual'),
(1,1,14,'2026-09-27',2500.00,'expense','Health insurance premium','HDFC Ergo','manual'),
-- August 2026
(1,1,1,'2026-08-01',85000.00,'income','Monthly Salary','HDFC Payroll','manual'),
(1,1,4,'2026-08-03',2900.00,'expense','Groceries','BigBazaar','manual'),
(1,3,5,'2026-08-05',2100.00,'expense','Restaurant dinner','Mainland China','manual'),
(1,3,9,'2026-08-05',649.00,'expense','Netflix subscription','Netflix','manual'),
(1,3,9,'2026-08-05',199.00,'expense','Spotify Premium','Spotify','manual'),
(1,1,13,'2026-08-06',18000.00,'expense','Monthly rent','Landlord','manual'),
(1,1,8,'2026-08-09',1100.00,'expense','Electricity bill','BESCOM','manual'),
(1,1,6,'2026-08-12',380.00,'expense','Petrol','HPCL','manual'),
(1,1,2,'2026-08-15',18000.00,'income','Freelance - logo design','Client XYZ','manual'),
(1,3,11,'2026-08-18',6500.00,'expense','Clothes shopping','Myntra','manual'),
(1,1,4,'2026-08-20',2400.00,'expense','Weekly groceries','Reliance Fresh','manual'),
(1,1,7,'2026-08-24',800.00,'expense','Streaming - HBO Max','HBO Max','manual'),
(1,1,10,'2026-08-28',3200.00,'expense','Dental checkup','Dental Clinic','manual'),
-- July 2026
(1,1,1,'2026-07-01',85000.00,'income','Monthly Salary','HDFC Payroll','manual'),
(1,1,4,'2026-07-02',3400.00,'expense','Groceries','BigBazaar','manual'),
(1,3,5,'2026-07-04',1600.00,'expense','Dining out','Cafe','manual'),
(1,3,9,'2026-07-05',649.00,'expense','Netflix subscription','Netflix','manual'),
(1,1,13,'2026-07-06',18000.00,'expense','Monthly rent','Landlord','manual'),
(1,1,8,'2026-07-10',1050.00,'expense','Electricity','BESCOM','manual'),
(1,3,3,'2026-07-12',5000.00,'income','Dividend payout','Zerodha','manual'),
(1,1,6,'2026-07-15',420.00,'expense','Auto rides','Ola','manual'),
(1,1,4,'2026-07-18',2700.00,'expense','Grocery shopping','DMart','manual'),
(1,1,12,'2026-07-22',3000.00,'expense','Books','Amazon','manual'),
(1,1,11,'2026-07-25',8000.00,'expense','New shoes','Nike Store','manual'),
(1,1,7,'2026-07-28',1400.00,'expense','Concert tickets','BookMyShow','manual');

-- ── Demo Budgets (September 2026) ─────────────────────────────────────────────
INSERT INTO budgets (user_id, category_id, amount, period_month) VALUES
(1, 4,  8000.00, '2026-09'),   -- Groceries
(1, 5,  5000.00, '2026-09'),   -- Dining Out
(1, 6,  2000.00, '2026-09'),   -- Transportation
(1, 9,  1500.00, '2026-09'),   -- Subscriptions
(1, 11, 5000.00, '2026-09'),   -- Shopping
(1, 7,  2000.00, '2026-09'),   -- Entertainment
(1, 10, 3000.00, '2026-09');   -- Healthcare

-- ── Demo Goals ────────────────────────────────────────────────────────────────
INSERT INTO goals (user_id, name, target_amount, current_amount, target_date, status, notes) VALUES
(1, 'Emergency Fund',       100000.00, 45000.00, '2027-03-31', 'active',   '6 months of expenses'),
(1, 'New Laptop (MacBook)', 150000.00, 80000.00, '2026-12-25', 'active',   'For work and personal use'),
(1, 'Goa Trip',              30000.00, 30000.00, '2026-10-15', 'achieved', 'Beach holiday!'),
(1, 'Investment Corpus',    500000.00, 95000.00, '2028-12-31', 'active',   'Index fund SIP goal');

-- ── Demo Recurring Obligations ────────────────────────────────────────────────
INSERT INTO recurring_obligations (user_id, category_id, merchant, expected_amount, frequency, is_subscription, last_payment_date, next_due_date, status, confidence_score) VALUES
(1, 9,  'Netflix',        649.00,   'monthly', 1, '2026-09-04', '2026-10-04', 'active', 1.00),
(1, 9,  'Spotify',        199.00,   'monthly', 1, '2026-09-04', '2026-10-04', 'active', 1.00),
(1, 13, 'Landlord',     18000.00,   'monthly', 0, '2026-09-06', '2026-10-06', 'active', 1.00),
(1, 8,  'BESCOM',        1100.00,   'monthly', 0, '2026-09-10', '2026-10-10', 'active', 0.90),
(1, 14, 'HDFC Ergo',     2500.00,   'monthly', 0, '2026-09-27', '2026-10-27', 'active', 1.00);
