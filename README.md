# FinanceManager — Safe to Spend

One question, one number: **"Can we spend this money right now without causing a checking
shortfall before the next paycheck or before upcoming bills hit?"**

Everything is in a single file: **[`safe-to-spend.html`](safe-to-spend.html)**.
Double-click it (or open it in any browser) — no install, no server, no account, works offline.
All data stays in that browser's local storage; nothing ever leaves your machine.

## The one rule it enforces (no double counting)

- A **card purchase** is the real spending event. The moment you log it, it raises that card's
  **unfunded** total on the Cards tab — but no cash leaves checking yet.
- The later **card payment** is a **transfer** of cash from checking to the card. It is never a
  second expense.
- Only rows that actually move cash drive the checking projection and the headline number — a
  new purchase only moves the headline **Safe to Spend** once it's actually scheduled to leave
  checking (Reconcile the card, or its statement due date falls before your next payday).
- Each card's **balance** and **funded** are always *calculated*, never typed in directly. Balance
  starts from whatever you last confirmed in **Reconcile**, as of the **"Balance as of"** date you
  set there, and rolls forward with every purchase, refund, and posted payment dated after that;
  funded is simply whatever payment is currently scheduled for that card. Unfunded is the gap. If
  you're backfilling a purchase that happened after your last statement closed, set "Balance as of"
  to the statement's real cutoff (not necessarily today) so the purchase is picked up correctly.
- Any **Pending** ledger item still dated more than 2 weeks in the past gets flagged in a banner —
  it's almost always a bill that already posted (mark it Cleared) or leftover cruft (delete it),
  and it silently drags your number down for as long as it sits there.
- A **debt paid via a credit card** (medical financing, a store card you settle with your rewards
  card, etc.) isn't a cash payment, so it can't use a card's normal "Pay from checking" schedule.
  Track it as a **Debt** card (its own balance on the Cards tab) and add its payment as a
  **Recurring bill** with **Pays down debt** set — each occurrence logs two linked ledger rows: a
  charge on the paying card (a real purchase) and a paydown on the debt, with no cash impact
  either way, exactly like the "no double counting" rule for any other card purchase. The two rows
  are tagged ("↳ pays down X" / "debt paydown") so they're never mistaken for duplicates — deleting
  either one prompts to delete both together, since one without the other leaves the numbers wrong.

## First run

The app loads sample seed data (from `SafeToSpend_Model.xlsx`) with a fixed "today" of
2024-07-12 so you can see it working: Safe to Spend = **$1,208.16** (Conservative), projected
low **−$1,296.19 on Aug 5**.

## Loading your real data

1. Open **Backup → Export to JSON** first if you ever want the sample back (or just use *Reset to seed data*).
2. **Accounts** tab: replace the sample accounts with yours and type in today's balances. Mark
   only checking as *spendable*.
3. **Cards** tab: click **+ Add card** for each one and enter what it currently owes as its
   starting balance, plus its statement/due date/autopay. That schedules the statement payment
   automatically — balance and unfunded take it from there on their own.
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
