-- =====================================================================
-- 20260930_sadmin_panel_2.sql
--
-- Follow-up to 20260930_sadmin_panel.sql, same day, written after the
-- subscription module was built. One table.
--
-- WHY THIS IS A SEPARATE FILE
-- The first migration is already applied to the live database. Re-running
-- it would fail on every CREATE TABLE, and editing it to include this
-- would make the file disagree with what is actually in the database. So
-- the schema stays a sequence of files, each of which can be applied in
-- order and each of which is a record of what happened when.
--
-- ADDITIVE ONLY.
-- =====================================================================

BEGIN;

-- ---------------------------------------------------------------------
-- tenant_payment_history
-- ---------------------------------------------------------------------
-- subscription_payments has ON DELETE CASCADE from subscriptions. That is
-- the right default for "this payment belongs to this billing period" and
-- the wrong one for money: cancelling a subscription would take the record
-- of what was paid with it.
--
-- Sadmin_subscriptions::api_cancel() copies the payments here before it
-- deletes the subscription row, so the answer to "did this parishad pay
-- anything" survives the end of their subscription. Nothing writes here
-- except that copy and the matching delete in api_delete_payment().
--
-- payment_id is deliberately NOT a foreign key to subscription_payments.
-- The whole point is that the row it referenced is gone. A FK would either
-- block the delete or cascade the archive away with it.
CREATE TABLE tenant_payment_history (
    archive_id     BIGSERIAL PRIMARY KEY,
    ins_titute_p_id INTEGER     NOT NULL REFERENCES ins_titute (ins_titute_p_idds) ON DELETE CASCADE,
    -- The id the payment had in subscription_payments. Nullable and not
    -- unique: a tenant whose subscription is cancelled twice could, in
    -- principle, have two archives carrying the same original id, and
    -- refusing that would be trading a real record for a tidiness.
    payment_id     BIGINT,
    invoice_no     VARCHAR(50) NOT NULL,
    amount         INTEGER     NOT NULL,
    payment_method VARCHAR(30) NOT NULL DEFAULT 'cash',
    reference      VARCHAR(100),
    paid_at        DATE,
    note           TEXT,
    -- When the archive row was written, which is not the same as when the
    -- payment was made. Both dates are kept because "when did we stop
    -- serving them" and "when did they pay" are different questions and an
    -- audit of a cancellation needs to answer both.
    archived_at    TIMESTAMPTZ  NOT NULL DEFAULT now(),
    created_at     TIMESTAMPTZ  NOT NULL DEFAULT now()
);

CREATE INDEX tenant_payment_history_tenant_idx ON tenant_payment_history (ins_titute_p_id, paid_at DESC NULLS LAST);
CREATE INDEX tenant_payment_history_invoice_idx ON tenant_payment_history (invoice_no);
CREATE INDEX tenant_payment_history_payment_idx ON tenant_payment_history (payment_id);

-- amount >= 0 here too, for the same reason subscription_payments_amount_chk
-- exists: a refund is not a negative payment, and this table is not where
-- that question gets decided.
ALTER TABLE tenant_payment_history
    ADD CONSTRAINT tenant_payment_history_amt_chk CHECK (amount >= 0);

COMMIT;
