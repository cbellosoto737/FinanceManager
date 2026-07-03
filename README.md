# FinanceManager — Safe to Spend

One question, one number: **"Can we spend this money right now without causing a checking
shortfall before the next paycheck or before upcoming bills hit?"**

Everything is in a single file: **[`safe-to-spend.html`](safe-to-spend.html)**.
Double-click it (or open it in any browser) — no install, no server, no account, works offline.
All data stays in that browser's local storage; nothing ever leaves your machine.

## The one rule it enforces (no double counting)

- A **card purchase** is the real spending event. It raises what you owe on the card the day it
  happens — but no cash leaves checking yet.
- The later **card payment** is a **transfer** of cash from checking to the card. It is never a
  second expense.
- Only rows that actually move cash drive the checking projection and the headline number, and
  each card tracks a **funded vs unfunded** statement portion so reserved card spending isn't
  counted twice.

## First run

The app loads sample seed data (from `SafeToSpend_Model.xlsx`) with a fixed "today" of
2024-07-12 so you can see it working: Safe to Spend = **$1,208.16** (Conservative), projected
low **−$1,296.19 on Aug 5**.

## Loading your real data

1. Open **Backup → Export to JSON** first if you ever want the sample back (or just use *Reset to seed data*).
2. **Accounts** tab: replace the sample accounts with yours and type in today's balances. Mark
   only checking as *spendable*.
3. **Cards** tab: add each card and hit **Reconcile** — statement balance, due date, autopay
   mode. That schedules each statement payment automatically.
4. **Recurring** tab: enter your repeating bills, then click **Generate upcoming bills**.
5. **Settings** tab: set paydays, net paycheck, cash reserve — and switch "Today" from the fixed
   seed date to **use the real current date**.
6. Delete the leftover sample ledger rows (Ledger tab), or Reset to seed first and rebuild clean.

## Keeping it accurate (the 10-minute weekly routine)

1. **Import CSVs** — download each card's and checking's CSV from the bank and drop them into
   **Import CSV**. Purchases, refunds, and card payments are classified automatically; rows that
   match pending items reconcile them instead of duplicating.
2. **Click the yellow banner** if it appears ("N upcoming bills aren't in the ledger") — one
   click keeps the projection honest.
3. **Update account balances** (Accounts tab) to whatever the bank shows — 30 seconds.

When a card statement arrives, **Reconcile** it (two fields, one click). A surprise bill goes in
via "Add something" on the Ledger tab in seconds.

Move between devices with **Backup → Export/Import JSON**.
