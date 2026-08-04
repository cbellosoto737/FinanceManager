# FinanceManager — Safe to Spend

One question, one number: **"Can we spend this money right now without causing a checking
shortfall before the next paycheck, before upcoming bills hit, or once every card gets paid
off?"**

Everything lives in a single file, **[`index.html`](index.html)**. Double-click it (or open it
in any browser) to try it standalone — no install, no server, no account, works offline, with
data kept in that browser's local storage only.

Deployed with the setup below (see **Multi-user access**), the same file instead signs in with
Google and reads/writes one shared copy of the data in a small MySQL database, so two people —
different devices, different browsers — see the exact same numbers. It still keeps a local
offline-safe cache and still works if the network drops, but the browser's local storage is no
longer the source of truth once this is deployed.

Works the same on a phone: under ~768px wide, the page switches to a bottom tab bar (Home, Ledger,
Cards, Accounts, More) instead of the desktop's row of tab buttons — Home is the dashboard (the
headline number, tiles, and 60-day runway); everything else that doesn't fit in the bar (Recurring,
Import CSV, Backup, Settings) lives behind **More**. Forms stack into single-column fields sized
for touch, and wide tables scroll horizontally within their own box instead of the whole page.

## The one rule it enforces (no double counting)

- A **card purchase** is the real spending event. The moment you log it, it raises that card's
  **unfunded** total on the Cards tab — but no cash leaves checking yet.
- The later **card payment** is a **transfer** of cash from checking to the card. It is never a
  second expense. You schedule it with the **Reconcile** button on the Cards tab: entering a card's
  new *statement* balance and due date also queues its payment in one step. In that dialog you set the
  **payment amount** (full / minimum / custom, per autopay), the **payment date** (defaults to the due
  date — set it earlier if you pay ahead), and whether the payment is **Scheduled** (Pending, still
  owed) or **Already paid** (Cleared, already left checking). The scheduled amount shows under
  **Scheduled to pay** on the Cards tab and lowers that card's *Unfunded* figure.
- The checking projection only reacts to rows that actually move cash — a new card purchase never
  double-counts there. But **Safe to Spend itself is a different, broader number**: it's the
  checking-based figure minus the **total unfunded card obligation**, so a card purchase lowers the
  headline the instant you log it, whether or not it's been folded into a scheduled statement
  payment yet. Reconciling a card only ever holds the headline steady or *improves* it (if the real
  due date turns out to land after your next payday) — it can never cause a surprise drop, because
  the debt was already subtracted the moment it happened.
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
  When you **Reconcile** a debt (as opposed to a normal card), the dialog swaps the "pay from checking"
  fields for a **Paid by recurring** picker: choose the recurring bill that clears this debt and its
  next charge becomes the debt's **Scheduled to pay** and due date — no checking payment is scheduled,
  so the reconcile stays tied to the recurring payment that actually pays it down.
- **Accounts** (checking/savings) work exactly like Cards: **Balance is calculated, not typed in.**
  It starts from whatever you set at **Balance as of** and rolls forward automatically with every
  **Cleared** cash-impact ledger entry touching that account dated on or after that day — mark a
  bill or paycheck Cleared once it actually posts and the balance updates itself; a still-**Pending**
  one stays out of it (it hasn't happened yet, so it belongs to the forward projection instead). Hit
  **Edit** to correct drift or set the starting point, with an explicit date — not necessarily today.
  This is what makes automating input later (CSV import, eventually a bank feed) work end to end: an
  imported transaction lands Cleared, and the balance it affects updates itself with no manual step.
- **Recurring bills** support **Kind: Income** for predictable inflows like a paycheck — set it up
  once (amount, cadence, which checking account it lands in) and **Generate upcoming bills** keeps
  depositing it every payday, the same low-maintenance way expenses work, instead of a manual
  "Add something → Money in" entry every pay period. One-off income (a bonus, a refund) still goes
  through quick-add on the Ledger tab. Frequency options include **Semimonthly** (twice a month,
  e.g. the 1st & 16th) and **Semiannual** (every 6 months) alongside Weekly/Biweekly/Monthly/Yearly.
- Every table (**Ledger, Cards, Accounts, Recurring**) has a **Show** filter above it and clickable
  column headers — click a header to sort by it, click again to reverse. Neither choice is saved
  between page loads; they're just a quick way to look at what's already there from a different
  angle (e.g. sort Cards by Unfunded to see what needs attention first, or filter Recurring to
  Income only).
- The **Unfunded card obligation** tile shows that total explicitly — "what's left if every card's
  un-autopaid spend hit today, not just what's already scheduled" — and its subtext makes clear
  it's already baked into the headline above, not a second, competing number to reconcile in your
  head.
- The **60-day runway** has a **Net after cards** column (toggle it with the checkbox above the
  table). It starts at today's checking minus your unfunded card debt (so day 1 lands right around
  your Safe-to-Spend number), then rolls forward — but it obeys the same *no double counting* rule as
  the rest of the app: a **card statement payment is neutral** to this line. Cash leaves checking (so
  the plain *Projected checking* column dips), but that payment just retires card debt you were
  already carrying, so your *net* position is unchanged. Real bills and income still move it, and a
  brand-new card purchase lowers it (fresh debt). The *Projected checking* column is literal cash;
  *Net after cards* is the more complete picture, and the one to watch if you don't want a card
  statement to sneak up on you. Uncheck the box for the classic cash-only view.

## The two clocks: "what's real right now" vs. "what's still coming"

Every cash-impact ledger row is in exactly one of two buckets, never both — this is what keeps the
whole app from ever double-counting a dollar:
- **Cleared** = it actually happened. It's baked into the relevant account's or card's **Balance**
  (as of that account/card's own "Balance as of" date) and is invisible to the projection/window
  from that point on. A Cleared row dated in the *future* is the one exception the app guards
  against — it waits until that date arrives before touching your balance, since "cleared" is
  supposed to mean *already happened* (this is why scheduling a payment for its due date and
  pre-marking it Cleared no longer yanks the cash out early).
- **Pending** = scheduled but hasn't happened yet. It's invisible to Balance and instead drives the
  60-day projection and the pre-payday "scheduled outflows/inflows" figure.
The instant something flips from Pending to Cleared, it moves from the second bucket to the first —
nothing needs to be re-entered, and nothing is ever counted in both places at once.

## First run

The app loads generic sample data built **relative to today** — a couple of checking accounts, a
few savings/investment accounts, five credit cards (one of them carrying a min-only balance so you
can see *unfunded* debt in action), and a spread of recurring bills and a biweekly paycheck. Because
it's anchored to the real current date, the demo never shows stale dates from some past year, and
every "add" dialog defaults to today. It's all placeholder data — replace it on the Accounts /
Cards / Recurring tabs, or **Reset to seed data** to bring the demo back.

## Loading your real data

1. Open **Backup → Export to JSON** first if you ever want the sample back (or just use *Reset to seed data*).
2. **Accounts** tab: replace the sample accounts with yours and type in today's real balances (this
   becomes the starting point everything rolls forward from). Mark only checking as *spendable*.
3. **Cards** tab: click **+ Add card** for each one and enter what it currently owes as its
   starting balance, plus its statement/due date/autopay. That schedules the statement payment
   automatically — balance and unfunded take it from there on their own.
4. **Recurring** tab: enter your repeating bills — and your paycheck too, with **Kind: Income** —
   then click **Generate upcoming bills**.
5. **Settings** tab: set paydays, net paycheck, cash reserve — and switch "Today" from the fixed
   seed date to **use the real current date**.
6. Delete the leftover sample ledger rows (Ledger tab), or Reset to seed first and rebuild clean.

## Keeping it accurate (the 10-minute weekly routine)

1. **Import CSVs** — download each card's and checking's CSV from the bank and drop them into
   **Import CSV**. Purchases, refunds, and card payments are classified automatically; rows that
   match pending items reconcile them instead of duplicating.
2. **Click the yellow banner** if it appears ("N upcoming bills aren't in the ledger") — one
   click keeps the projection honest.
3. **Mark things Cleared** as they actually post (bills, the paycheck) — account balances update
   themselves from that. Only retype a balance if it's drifted from what your bank actually shows.

When a card statement arrives, **Reconcile** it (two fields, one click). A surprise bill goes in
via "Add something" on the Ledger tab in seconds.

Running standalone (no backend deployed), move between devices with **Backup → Export/Import
JSON**. Once the multi-user backend below is deployed, both people's devices sync automatically —
Export/Import JSON is then just an extra safety-net backup, and the one-time way to migrate
whatever you already had in local storage into the shared database (see below).

## Multi-user access (Google Sign-In + Hostinger)

By default this app is 100% client-side. To let two people (e.g. spouses) share one set of data
from their own devices, deploy the small PHP + MySQL backend included in this repo (`api/`,
`schema.sql`, `config.sample.php`) alongside `index.html` on Hostinger shared/Business hosting.
Nothing about the calculation engine or the tabs changes — only *where* the data lives (a MySQL
row instead of `localStorage`) and *who* can open it (Google Sign-In, restricted to an allowlist
of exactly the email addresses you configure).

### One-time setup

1. **Google Cloud Console** → create/select a project → **APIs & Services → OAuth consent
   screen**: type **External**, publishing status **Testing** (leave it in Testing — don't click
   Publish; Testing supports up to 100 named test users indefinitely and skips Google's app
   verification process). Add both household email addresses as test users.
2. Still in Google Cloud Console → **Credentials → Create Credentials → OAuth Client ID** → type
   **Web application** → under *Authorized JavaScript origins* add your real site URL
   (`https://yourdomain.com`). No redirect URI is needed. Copy the resulting **Client ID**.
3. In `index.html`, set `GOOGLE_CLIENT_ID` (near the top of the second `<script>` block) to that
   Client ID.
4. In **Hostinger hPanel**: create a MySQL database and a database user scoped to it; confirm SSL
   is active and HTTP requests are force-redirected to HTTPS (required — login cookies won't be
   sent otherwise); set the PHP version to 8.1 or newer.
5. Open that database in **phpMyAdmin** and run everything in `schema.sql`.
6. Copy `config.sample.php` to `config.php`, fill in the database credentials, the same Google
   Client ID, and `ALLOWED_EMAILS` (the two addresses allowed to sign in). Upload this file **one
   directory above** `public_html` (e.g. `domains/yourdomain.com/config.php`) — never inside
   `public_html` itself. After uploading, request that exact path in a browser and confirm it
   returns a 404; if it doesn't, move it and fix the path before going further.
7. Upload `index.html` and the whole `api/` folder into `public_html/`.
8. Visit the site. You should see a **sign-in screen**, not the app. Sign in with an allowlisted
   Google account — it should load the (empty/demo) data and the app should now show "Signed in
   as …" under Settings.

### Bringing over your existing data

If you'd already been using the standalone version and have real data sitting in one browser's
local storage:

1. On that browser, **before** it loads the newly deployed version: **Backup → Export to JSON**.
2. On the deployed, signed-in app: **Backup → Import from JSON**, choose that file. This is the
   normal Import feature — it now also syncs straight to the shared database.
3. The second person just signs in on their own device; they don't import anything — they'll
   automatically pull the copy that's now on the server.

### How it behaves day to day

- Saves sync to the server automatically a moment after you make a change; a small localStorage
  copy is kept as an offline fallback so the app still opens if the network is down.
- If both of you happen to save around the same moment, the person whose save landed second sees
  a banner saying changes were saved elsewhere, with a one-click reload — nothing is silently
  overwritten.
- Every save keeps the previous version in a small history table server-side, so a bad import or
  a mistake is recoverable, not just a bug for the two of you to work around by hand.
- **Settings → Sign out** ends your session on that device.
