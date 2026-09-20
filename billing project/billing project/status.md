# FinPilot — Current Project Status

Audit date: 2026-09-19  
Scope: repository source, schema/seed files, local database connectivity, and selected live HTTP checks at `http://localhost:8000`. This is an evidence-based snapshot, not a roadmap claim.

## Status key

| Status | Meaning |
|---|---|
| 🟢 Working / Verified | Code path was exercised successfully in this audit. |
| 🟡 Implemented / Needs Verification | Code and UI exist, but its mutation or complete workflow was not run. |
| 🟠 Partially Implemented | Some necessary layers exist, but material capability is absent or incomplete. |
| 🔴 Broken / Blocked | A confirmed runtime/code defect prevents the advertised workflow. |
| ⚪ Not Implemented | Required capability has no implementation. |
| 🔵 Out of Scope | Deliberately not a FinPilot requirement. |

## Audit evidence and boundaries

The repository is a PHP 8 MVC monolith: `public/index.php` registers routes; controllers use `App\Core\Controller`; PDO access is centralized in `app/Core/Database.php`; and views are PHP templates under `views/`. There is no Composer manifest, PHPUnit configuration, test directory, external API SDK, background worker, or JavaScript build system.

The following were actually verified against the running local application and its configured `finpilot_db` database:

- Database connectivity; the `users` table contains demo user ID 1.
- `demo@finpilot.app` / `finpilot123` authenticates, establishes a session, and finishes at `/dashboard`.
- `/dashboard`, `/accounts`, `/transactions`, `/budgets`, `/goals`, `/recurring`, `/import`, `/agent`, `/reports`, and `/settings` returned HTTP 200 while authenticated.
- Logout ends at `/login`; a subsequent protected dashboard request redirects to `/login`.
- Registration completed to `/accounts`; the new account subsequently logged in to `/dashboard`. Temporary test users were removed.
- The agent endpoint answered “Where did I spend the most this month?” with HTTP 200, `CATEGORY_SPENDING`, and non-empty user-scoped data.

Rendering a page does not prove its create/update/delete workflow. No financial data was added, edited, imported, or reset during this status audit.

## What is actually working?

- 🟢 Login, session creation, protected-route redirect, and logout: `AuthController`, `User::authenticate`, and `Session` were exercised.
- 🟢 Registration hashes a password through `password_hash()` and was exercised through registration, logout, and a subsequent login.
- 🟢 Authenticated major-page navigation renders all ten primary FinPilot pages listed above.
- 🟢 The deterministic, user-scoped agent answered one supported category-spending question using transaction data.
- 🟢 Demo data, dashboard/report read queries, and agent reads are connected to the configured database.

## What exists but is not yet verified?

- 🟡 Account CRUD UI and model/controller paths.
- 🟡 Budget create/update/delete and utilization calculations.
- 🟡 Manual recurring-obligation CRUD and projected monthly amount.
- 🟡 Reports, CSV transaction export, month comparison, and unusual-spending output.
- 🟡 Profile update and password-change settings workflows.
- 🟡 Other supported agent intents: monthly summary, budget status, recurring obligations, goal progress, unusual spending, and safety rejection.

## What is broken?

`app/Core/Database.php` has no `execute()` method. The following code calls that nonexistent method, yielding a fatal `Call to undefined method App\Core\Database::execute()` when reached:

- `app/Models/Account.php`: `adjustBalance()` and `recalculateBalance()`.
- `app/Models/Goal.php`: `deposit()`.
- `app/Controllers/SettingController.php`: `resetData()`.

Consequences:

- 🔴 Manual transaction create, update, and delete are blocked because `TransactionService` adjusts account balances through `Account::adjustBalance()`.
- 🔴 Confirmed CSV import is blocked for the same reason after transaction insertion/balance adjustment begins.
- 🔴 Goal deposits are blocked.
- 🔴 “Reset all financial data” is blocked.

This is independent of authentication; authentication is currently working. It is the principal backend blocker for data-mutating flows.

## What is missing?

- ⚪ No automatic recurring-payment or subscription detection from transaction history. Recurring records are manually entered or seeded only.
- ⚪ No bill/PDF/image/Excel parsing or bill analysis. The import accepts only CSV/TXT statement files.
- ⚪ No analysis that calculates how present spending patterns affect a goal or a required contribution plan.
- ⚪ No category-management route/UI; users can use system/default categories, but cannot create/edit their own categories through the application.

## What is intentionally not included?

- 🔵 Invoice generation, receipt generation, invoice/receipt PDFs, payment collection, customer management, product catalogues, and business billing. These are BillFlow-era concepts, not FinPilot problem-statement requirements.
- 🔵 Investment recommendations, stock picks, crypto advice, trading signals, tax advice, legal advice, and guaranteed-return/wealth-management advice.

## Module status

Counts use the 22 rows below: **4 verified, 7 implemented/needs verification, 3 partial, 4 broken, 3 missing required capabilities, and 1 out of scope.**

| Module | Evidence / relevant files | Status |
|---|---|---|
| Authentication | `AuthController::login`, `User::authenticate`, `Session::login`; login runtime-tested | 🟢 |
| Registration | `AuthController::register`, `User::register`; runtime-tested | 🟢 |
| Session and logout | `Session.php`, `AuthController::logout`; runtime-tested | 🟢 |
| Dashboard | `DashboardController::index`, `Transaction`, `BudgetService`; authenticated render tested | 🟢 |
| Accounts | `AccountController`, `Account`, `views/accounts/index.php`; render only, CRUD not tested | 🟡 |
| Transaction ledger | `TransactionController`, `TransactionService`, `Account`; mutations call missing `execute()` indirectly | 🔴 |
| Categories | `Category` supplies system/user-visible categories; no category CRUD route/view | ⚪ |
| CSV statement import | `ImportController`, `ImportService`, `StatementImport`; parser exists, confirmation is blocked by missing `execute()` | 🔴 |
| Manual recurring obligations | `RecurringController`, `RecurringObligation`; CRUD/UI exists, not exercised | 🟡 |
| Automatic recurring/subscription detection | No detector invokes transaction history to create obligations | ⚪ |
| Budget management | `BudgetController`, `Budget`, `BudgetService`; CRUD/calculation code present, untested | 🟡 |
| Financial goals | Goal CRUD exists; `Goal::deposit()` is broken | 🟠 |
| Goal spending-impact analysis | No service/query links spending behavior to a goal forecast | ⚪ |
| Reports and month comparison | `ReportController`, `AnalysisService`; render tested, calculations/export untested | 🟡 |
| Unusual spending | 1.5× three-month category comparison in `AnalysisService`; not runtime-exercised | 🟡 |
| Personalized insights | Deterministic agent templates combine user data with explanatory text; one intent tested | 🟠 |
| Natural-language questions | Keyword intent classifier and `/agent/query`; category-spending intent tested | 🟢 |
| Monthly financial summary | Agent has `toolMonthlySummary`; not exercised | 🟡 |
| Settings: profile/password | `SettingController::update`, `User::changePassword`; not exercised | 🟡 |
| Settings: reset data | `SettingController::resetData` calls missing `Database::execute()` | 🔴 |
| Transaction CSV export | `ReportController::export`; code exists, not exercised | 🟡 |
| Invoice/receipt/billing | Not implemented because it is outside FinPilot scope | 🔵 |

## Problem Statement Coverage

| Requirement | Current implementation and files | DB support | UI / runtime evidence | Gaps | Final status |
|---|---|---|---|---|---|
| Upload statements, bills, and expense records | CSV/TXT import in `ImportController` and `ImportService`; manual transaction form | `statement_imports`, `transactions` | Import UI renders; manual form exists | No bills/PDF/images/Excel; confirmed import blocked | 🟠 |
| Automatically categorize transactions | Keyword-to-fixed-category-ID map in `ImportService::guessCategory()` | `transactions.category_id`, `categories` | Import preview supports it | Only hard-coded merchant keywords; import confirmation blocked | 🟠 |
| Identify recurring payments/subscriptions | Manual `RecurringObligation` records, seeded subscriptions, projected-cost query | `recurring_obligations` | Recurring page renders | No history-based automatic detection | 🟠 |
| Detect unusual spending | `AnalysisService::detectUnusualSpending()` compares current category spending to prior 3-month average | transactions/categories | Report page renders; not behavior-tested | No automated test/runtime output evidence | 🟡 |
| Monthly income/expense summary | `Transaction::monthlyTotals`, dashboard/report and agent monthly tool | `transactions` | Dashboard/render verified; monthly agent intent untested | Depends on ledger mutations currently blocked | 🟡 |
| Upcoming obligations | `RecurringObligation::upcomingForUser()` and Agent recurring tool | `recurring_obligations` | Dashboard page renders | Input is manual/seeded, not detected | 🟡 |
| Budget vs actual | `Budget::withSpendingForMonth`, `BudgetService::monthSummary` | `budgets`, transactions/categories | Budget page renders | Workflow/calculation not run | 🟡 |
| Financial goals | CRUD plus `Goal::progressPct` | `goals` | Goals page renders | Deposits broken; no spending impact analysis | 🟠 |
| Explain spending impact on goals | Goal progress answer only | goals/transactions exist separately | Goal UI exists | No cross-domain analysis | ⚪ |
| Personalized spending insights | Deterministic answers in `AgentService` | Reads user-scoped financial records | One category insight verified | No generative AI/provider; limited keyword intents | 🟠 |
| Natural-language questions | Keyword classifier dispatches to six read tools | Reads transactions, budgets, goals, recurring rows | One query verified | No semantic parsing, date extraction, follow-up context, or provider | 🟢 |
| Monthly summary with observations/action items | `AgentService::toolMonthlySummary()` gives income/expense/net/rate and templated text | transactions | Code present | Not runtime-tested; no scheduled/month-end generation | 🟡 |

## Database audit

Schema sources: `database/schema.sql` (schema only), `database/seed.sql` (seed data), and `database/database.sql` (combined). The live configured database was reachable as `finpilot_db`.

| Table | Purpose and key fields | Relationships / problem-statement mapping |
|---|---|---|
| `users` | Identity, email, bcrypt `password`, currency, `last_login` | Parent of user-owned records; auth/settings |
| `accounts` | Named account, type, balance, institution, active status | `user_id → users`; transaction ledger/accounts |
| `categories` | System defaults (`user_id NULL`) and potential user categories, type/icon/color | Referenced by transactions, budgets, recurring obligations; categorization |
| `transactions` | Date, amount, income/expense type, merchant, source, `import_hash` | User/account/category FKs; ledger, analytics, import; unique import hash |
| `budgets` | Category amount per `YYYY-MM` period | User/category FKs; unique user/category/month; budget comparison |
| `goals` | Target/current amounts, target date, status, notes | `user_id → users`; goal tracking |
| `recurring_obligations` | Merchant, expected amount, frequency, subscription flag, due dates, confidence | User/category FKs; bills/subscriptions |
| `statement_imports` | Import account, filename, row totals and duplicate count | User/account FKs; import history |

Foreign keys use InnoDB and user-owned child data uses cascading deletes where appropriate. The schema deliberately drops old `payments`, `invoice_items`, `invoices`, `products`, `customers`, `settings`, and `activity_logs` before creating FinPilot tables; it does not create any legacy billing table.

## Seed/demo data audit

`database/seed.sql` supplies one demo user (`demo@finpilot.app`), four accounts, fifteen default categories, three months of transactions, September budgets, four goals, and five recurring obligations. Netflix and Spotify demonstrate seeded subscriptions; historical July–September expense data supports budget, comparison, and anomaly-query demonstrations.

The corrected seeded bcrypt hash and live user were verified for `finpilot123`. Demo data supports reads for monthly/category spending, subscription display, recurring obligations, budgets, goals, month comparison, and unusual-spending calculations. It does **not** prove automated categorization, automatic recurring detection, AI behavior, or import success.

## Import, analytics, recurring, and goal detail

- **Import formats:** only `.csv` and `.txt` extensions, parsed with `fgetcsv`. Input must contain headers; the UI lets the user map date, description, amount, and type. No XLS/XLSX, PDF, image, OCR, bank API, email, or bill parser exists.
- **Duplicate detection:** SHA-256 of date/description/amount/type is checked against the globally unique `transactions.import_hash`. This is implemented but unverified and may treat matching transactions across different users as duplicates.
- **Categorization:** simple case-insensitive merchant keyword rules map to fixed seeded IDs. There is no ML/API categorizer and no fallback review/editor in preview.
- **Recurring/subscriptions:** the UI manually records frequency, due dates, subscription flag, and fixed `confidence_score = 1.00`. The seed adds records. No code derives these fields from transaction history or calculates next dates automatically.
- **Analytics:** category monthly totals, six-month chart data, previous-month comparison, and a >1.5× prior-three-month anomaly rule are implemented as database queries. Reports exposes them; none were functionally asserted beyond page rendering.
- **Goals:** users can create/edit/delete and view progress. A deposit action is present but blocked by the missing database method. No model estimates future affordability, spending trade-offs, or required contribution rate.

## AI agent audit and financial-safety boundary

`AgentService` is **not connected to an external AI provider**: there is no API key, HTTP client, prompt template, tool-calling protocol, model configuration, or generative model integration. It is a deterministic, keyword-based decision-support engine grounded in the authenticated user ID passed by `AgentController::query`.

Supported intents read user-scoped monthly totals, category spending, budget status, recurring obligations, goal progress, and unusual spending. The tested category question returned `CATEGORY_SPENDING` with user data. Unsupported wording falls back to a generic monthly snapshot; it is not an open-ended conversational agent.

The service rejects a fixed list of investment/tax/loan-related phrases and appends a non-advisory disclaimer. That is a useful boundary, but it is keyword-based rather than comprehensive; for example, policy enforcement is not centralized across all rendered content.

## Legacy BillFlow audit

Legacy domain code, routes, models, views, credentials, and database names were not found. Remaining occurrences are only the schema comments and `DROP TABLE IF EXISTS` statements used to remove old billing tables. They are harmless migration cleanup and do not affect FinPilot runtime behavior. Invoice/receipt/payment/customer/product features are absent and appropriately out of scope.

## UI and route audit

The sidebar exposes Dashboard, Transactions, Accounts, Budgets, Goals, Recurring Bills, Import Statement, AI Decision Agent, Reports, and Settings. Login and registration are separate views. `public/index.php` registers GET/POST routes for the expected operations, plus `/health/db`; no API versioning or external integration routes exist.

All primary authenticated GET pages were reached successfully in this audit. This confirms navigation/rendering, not every control, link, chart, form submission, file upload, or export download.

## Security audit

| Area | Current state |
|---|---|
| Passwords | Registration/change use `password_hash(... PASSWORD_BCRYPT, cost 12)`; login/change use `password_verify()`. Verified demo login uses a corrected valid bcrypt hash. |
| Sessions | App starts named session; login regenerates ID; session has `user_id`, `user`, and `logged_in`; logout destroys the session. Verified. |
| CSRF | POSTs are globally checked with `Session::verifyCsrf()`, but **any request with `X-Requested-With` bypasses this check** in `public/index.php`. The agent endpoint can therefore be called without a token when that header is supplied. 🔴 Security gap. |
| SQL injection | Values use prepared PDO statements. Dynamic table/column strings mostly originate from fixed model properties; no obvious raw request-to-SQL interpolation found. |
| User isolation | Read/list/update/delete paths commonly use `findForUser`/`whereUser`. However, transaction create/update and import do not verify that submitted `account_id` belongs to the current user before balance operations; imported duplicate hashes are global. 🟠 Needs adversarial verification/fix. |
| XSS | Views generally use `e()`. Agent output is inserted client-side and requires further review of its rendering/sanitization. |
| Upload validation | Extension check only (`csv`/`txt`), no MIME, size, CSV-row limit, or account-ownership validation; upload is stored under `storage/imports`. 🟠 |
| Secrets/config | `.env` is not tracked in the source listing; DB config supports env values and local defaults. No AI API key integration exists. |
| Debug/error behavior | `APP_DEBUG` defaults to true in `config/config.php`, while `public/index.php` suppresses browser error display and logs errors. Production defaults should be reviewed. |

## Testing audit

No automated test framework, PHPUnit config, test directory, CI configuration, or dedicated integration test script was found. `scripts/seed.php` is a seeder, not a test. Modified authentication PHP files were linted in the preceding authentication fix; this audit did not modify application code.

Tests actually run for this audit are listed in “Audit evidence and boundaries.” Unrun workflows include account CRUD, transaction CRUD, every import phase, recurring CRUD, budget CRUD, goals CRUD/deposit, report export, settings updates, multi-user authorization, malformed uploads, and all agent intents except category spending.

## Recommended Verification Roadmap

1. Fix the missing `Database::execute()` integration (or replace callers with supported methods), then run transaction CRUD, import confirmation, goal deposit, and reset-data tests.
2. Add account-ownership checks to transaction creation/update and import; test two-user isolation and per-user duplicate behavior.
3. Exercise account, budget, recurring, goal, settings, report-export, and all agent-intent workflows end to end.
4. Harden CSRF so AJAX requests verify a token, and harden uploads with size/MIME/content validation.
5. Implement or explicitly scope out automatic recurring/subscription detection, bill/PDF/image support, and goal-impact analysis; then add automated integration coverage.

## Overall Project Status

The BillFlow-to-FinPilot domain migration is substantively complete: application naming, navigation, tables, data model, and routes are all FinPilot-oriented, with legacy billing retained only as schema cleanup. The database schema and seeded demo data are ready for read-oriented demonstrations. Authentication, registration, core page rendering, and one deterministic data-grounded agent intent have runtime evidence.

The project does demonstrate the problem statement’s finance-dashboard concept clearly, but it is not yet a reliable complete decision-support system. The missing database method blocks core mutations, imports, deposits, and reset; automatic recurring detection, bill/PDF/image ingestion, and goal-impact analysis are absent; the “AI” layer is deterministic rather than provider-backed; and security/isolation hardening plus automated tests remain necessary before claiming production readiness.
