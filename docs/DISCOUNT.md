# DISCOUNT (ছাড়) — how it works, and what it deliberately does NOT do

Status: shipped 2026-10-01. Owner decisions: per-invoice · whoever
collects tax may grant it · invoice only, never the deposit side.

Read this before changing anything here. The design has three places
where the "obvious" change is the wrong one, and each is called out
below with the reason.


## 1. The two columns already existed

`collect_amnt_invoice` has carried these since the table was written:

    discount_amounts    varchar(11)     0 of 1659 rows populated
    discount_refs_name  varchar(200)    0 of 1659 rows populated

Nothing ever read or wrote them. There was no discount feature — only
the shape of one. No migration was needed and none was written.

`discount_amounts` is **varchar, not integer**. Any query that sums it
needs `NULLIF(discount_amounts,'')::int`, or an empty string becomes a
cast error instead of a zero.


## 2. Who may grant a discount

Whoever can already press "আদায় করুন" — chairman, socib, collector,
everyone. There is deliberately **no role gate**.

This is not a missing permission check. The owner chose it: whoever
takes the money in this parishad may forgive part of it. Gating it would
mean a collector could collect but not discount, which is not the rule.

The platform super-admin has nothing to do with it. The super-admin
panel does not collect tax and has no invoice form; the whole feature
lives in the tenant admin shell. Do not add a sadmin entry point.


## 3. The one write path

    Dashboard::insert_tax_paids()          Dashboard.php
      reads POST: person, amounts, entry_sals[], entry_stk[]
                 + discount_amount, discount_reason
      -> FamilyModel::insert_invoice_ss()  FamilyModel.php
      -> collect_amnt_invoice

Called from one place only: views/back/tax_collection.php, the collect
modal, url `taxs_paid`. It is the only place an invoice row is created,
so it is the only place that needed to change.

Validation, all three rules, all **before** the insert — a rejected
discount must not leave a half-written receipt:

    discount < 0              -> reject
    discount >= bill          -> reject
    discount > 0, no reason   -> reject

The reason is capped to the column's own 200 characters.

Client-side checks in tax_collection.php are a courtesy to whoever is at
the counter, not a gate. The same rules are re-checked server-side.


## 4. WHAT IS STORED, AND THE THING NOT TO CHANGE

    amnt_taka          = the NET — what was actually taken
    discount_amounts   = the discount
    discount_refs_name = the reason

Gross is not stored. It is recoverable as `amnt_taka + discount_amounts`,
and "how much has been forgiven in total" is a SUM over
discount_amounts. No extra column is needed for either.

**The net is computed on the server.** The browser sends the GROSS as
`amounts` and the discount separately. An earlier version of this had
the form subtract before posting while the server also subtracted, which
applied the discount twice and under-charged. If you touch the JS, keep
one side authoritative for the gross — the form must not pre-subtract.


## 5. Where it is shown

Three places, and **only on a bill that actually had a discount**:

    views/back/single_view.php                     holding detail screen
      মূল - ছাড় - কারণ
    views/download/print_recite_view_files.php     A4 money receipt
      সর্বমোট400 + ছাড় -100 + মূল পরিমাণ 500 + কারণ
    views/back/print_slip.php                      thermal / POS slip
      মূল পরিমাণ 500 + ছাড় -100

The single condition guarding all three is `discount > 0`. All 1659
pre-existing receipts have NULL, so printing the line unconditionally
would put a misleading "mOla 200" on every receipt in the system. The
reason is shown on screen and on the A4 receipt, but not on the POS
slip: on a 58mm slip every line costs vertical space, and "ছাড়: -100"
without context is not useful there.

The model methods behind these were given the two columns in their
select list — `get_invoiceByPersonID` and `get_invoiceByVocherNo`.
If you add a fourth surface, that select list is the thing to update.


## 6. The deliberate consequence — ARREARS DO NOT FALL

This is the one to read twice.

    total_unpaid = (bokeya_year * holding_rates_yearly) - paid_sum

`paid_sum` comes from `amount_paid.paid_amount`, which is inserted from
the **form's** `entry_stk` and is not touched by the discount.

So a Tk 600 bill discounted to Tk 500 records 500 on the receipt, and
the holding still shows 600 outstanding. **The citizen was relieved of
Tk 100 once; they were not granted a permanent reduction.** The owner
chose this explicitly — a discount belongs to one invoice, not to the
holding's tax rate forever.

Nothing writes to `holding_rates_yearlys` and nothing should.

**Do not "fix" this by writing `$net` into `amount_paid.paid_amount`.**
It looks like a one-line fix and it is not: the net covers several years
at once, so subtracting it from `paid_sum` would understate what was
paid in every one of those years.

Doing it properly needs a per-holding, per-year discount ledger — a new
table, not a column on the invoice — because arrears would have to
subtract amounts forgiven for the specific years they were granted.
That is separate work, deliberately not done here.


## 7. Queries worth having

Total forgiven by a tenant:

    SELECT COALESCE(SUM(NULLIF(discount_amounts,'')::int),0) AS total_discount
    FROM collect_amnt_invoice
    WHERE discount_amounts IS NOT NULL
      AND ins_ti_tute_pridd = 1;

Collected / forgiven / billed side by side, which is what a report
actually wants:

    SELECT
      COALESCE(SUM(amnt_taka::int),0)                                   AS total_collected_net,
      COALESCE(SUM(NULLIF(discount_amounts,'')::int),0)                AS total_discount_given,
      COALESCE(SUM(amnt_taka::int
            + COALESCE(NULLIF(discount_amounts,'')::int,0)),0)          AS total_billed_gross
    FROM collect_amnt_invoice
    WHERE ins_ti_tute_pridd = 1;

`ins_ti_tute_pridd` is the column on the invoice table.
`ins_ti_tute_p_idds` does not exist. Omitting the tenant filter mixes
every parishad's discounts into one figure.

`date_s_today_s` is a varchar, so wrap it for a monthly report:
`to_char(date_s_today_s::date,'YYYY-MM')`.


## 8. Pre-existing, NOT part of this feature

Found while building it, left alone, listed so nobody re-investigates:

- **`invoice_no_s` is `time()`** — second resolution. Two collections in
  the same second share a number. One number in the table appears four
  times. This predates the discount work and is a real audit hazard: fix
  it deliberately, with a `pg_dump` first, not as a drive-by.

- **`single_entry_view` takes `uniqID`, not the holding number.** The
  URL parameter is `uniq_id` and it wants
  `family_data_sheet.uniqID` (a hash like `64e4405e69d06-…`), NOT
  `holding_number`. Passing a holding number returns HTTP 500. This
  predates the discount work and is easy to mistake for a regression.

- **`recipt_print` returns HTML, not a PDF.** mPDF is not on that path;
  the browser's print dialog produces the PDF. Bengali rendering in that
  print is therefore the browser's business and has not been verified by
  anyone here. Verify before promising a Bengali PDF.


## 9. What is NOT done

Honest list, so a later session does not assume any of it exists:

- **No report or dashboard surface.** The totals in section 7 are run by
  hand. Nothing in the UI shows "total forgiven" — not the dashboard, not
  the report page, not the settings page. The data is there; nobody looks
  at it on screen yet.
- **The POS slip does not show the reason** (section 5).
- **Bengali in the printed PDF is unverified** (section 8, third item).
- **Bengali numerals on screen are unverified by eye.** The converters
  (`bn_convert`, `BanglaConverter::en2bn`) were exercised through `curl`
  and the rendered HTML was inspected, but nobody has looked at a rendered
  page in a browser. The discount block sits inside a modal that is
  rebuilt on every open, and the client state resets on `shown.bs.modal`
  — that was reasoned about, not clicked through.

## 10. See also

- `application/views/back/tax_collection.php` — the form, and the
  client-side arithmetic
- `application/controllers/Dashboard.php` — `insert_tax_paids()`, the
  validation and the long comment on why arrears do not fall
- `application/helpers/approval_helper.php` — a different feature, but
  read its "Reading the current user" note; the same `ion_auth->user()`
  trap appears in both
