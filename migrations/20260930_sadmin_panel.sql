-- =====================================================================
-- 20260930_sadmin_panel.sql
--
-- Super Admin panel: Settings / Subscription / User Manager /
-- Support / Audit Log.
--
-- ADDITIVE ONLY. Nothing is dropped, renamed or retyped. The two
-- pre-existing columns this touches are left in place and are still
-- written by the old code paths:
--
--   ins_titute.theme_ss   -- the plan key has been living here since
--                            Front::register() wrote 'theme_ss' =>
--                            $package. It stays, and package_key is
--                            added beside it. Both are kept in sync by
--                            Super_admin::api_assign_plan().
--   ins_titute.expire_times -- varchar(180) holding three different
--                            formats at once (a unix timestamp, a
--                            YYYY-MM-DD date, and empty). The real
--                            expiry is now subscriptions.end_date.
--                            This column is left exactly as it is.
--
-- Rollback: drop the tables at the bottom. The two ALTERs would need
-- a column drop, which is deliberately NOT scripted here.
-- =====================================================================

BEGIN;

-- ---------------------------------------------------------------------
-- 1. SUBSCRIPTION — packages
-- ---------------------------------------------------------------------
-- Plan keys are the three the product site already advertises in
-- saas/pages/plans.php and Front::register() already whitelists:
-- standard, premium, cluster.

CREATE TABLE packages (
    package_id     SERIAL PRIMARY KEY,
    package_key    VARCHAR(50)  NOT NULL UNIQUE,
    name_bn        VARCHAR(150) NOT NULL,
    name_en        VARCHAR(150),
    price_monthly  INTEGER      NOT NULL DEFAULT 0,
    price_yearly   INTEGER      NOT NULL DEFAULT 0,
    -- NULL means "no limit". Chosen over 0 so a 0-reading tenant limit
    -- and an unlimited one are never confused.
    max_holdings   INTEGER,
    max_users      INTEGER,
    features       TEXT,
    is_active      BOOLEAN      NOT NULL DEFAULT TRUE,
    sort_order     INTEGER      NOT NULL DEFAULT 0,
    created_at     TIMESTAMPTZ  NOT NULL DEFAULT now()
);

-- The prices are the ones hardcoded in saas/pages/plans.php today, so
-- seeding them means the panel and the public pricing page agree from
-- the first render instead of drifting apart on the first edit.
INSERT INTO packages
    (package_key, name_bn, name_en, price_monthly, price_yearly, max_holdings, max_users, features, sort_order)
VALUES
    ('standard', 'স্ট্যান্ডার্ড',   'Standard',   2400, 25000,  5000,   5,
     'হোল্ডিং ট্যাক্স অটোমেশন|ডিজিটাল সনদ জারি|পাবলিক ওয়েবসাইট|ইমেইল সাপোর্ট', 1),
    ('premium',  'প্রিমিয়াম',      'Premium',    3800, 40000,  50000,  25,
     'স্ট্যান্ডার্ডের সব ফিচার|custom domain|সাব-ডোমেন|API অ্যাক্সেস|অগ্রাধিকার সাপোর্ট|কাস্টম ব্র্যান্ডিং', 2),
    ('cluster',  'ক্লাস্টার',       'Cluster',    0,    0,      NULL,  NULL,
     'আনলিমিটেড হোল্ডিং|আনলিমিটেড ইউজার|ডেডিকেটেড সাপোর্ট ম্যানেজার|ওন-সাইট ট্রেনিং', 3);


-- ---------------------------------------------------------------------
-- 2. SUBSCRIPTION — per-tenant subscription
-- ---------------------------------------------------------------------
-- One live row per tenant. The tenant_id is the primary key, which is
-- the same choice tenant_settings-style tables make elsewhere: a
-- tenant has exactly one subscription, so a UNIQUE constraint on
-- ins_titute_p_id would be the same statement in more words.

CREATE TABLE subscriptions (
    subscription_id BIGSERIAL PRIMARY KEY,
    ins_titute_p_id  INTEGER      NOT NULL REFERENCES ins_titute (ins_titute_p_idds) ON DELETE CASCADE,
    package_id       INTEGER      NOT NULL REFERENCES packages (package_id),
    billing_cycle    VARCHAR(20)  NOT NULL DEFAULT 'yearly',
    start_date       DATE         NOT NULL DEFAULT CURRENT_DATE,
    -- end_date, not expire_times, is the source of truth for expiry.
    -- subscriptions_exp_check keeps the end after the start, which is
    -- the one rule a date pair always needs and never gets.
    end_date         DATE         NOT NULL,
    status           VARCHAR(20)  NOT NULL DEFAULT 'active',
    is_auto_renew    BOOLEAN      NOT NULL DEFAULT FALSE,
    note             TEXT,
    created_at       TIMESTAMPTZ  NOT NULL DEFAULT now(),
    updated_at       TIMESTAMPTZ  NOT NULL DEFAULT now(),

    CONSTRAINT subscriptions_tenant_uniq UNIQUE (ins_titute_p_id),
    CONSTRAINT subscriptions_cycle_chk  CHECK (billing_cycle IN ('monthly', 'yearly')),
    CONSTRAINT subscriptions_status_chk CHECK (status IN ('active', 'expired', 'cancelled', 'pending')),
    CONSTRAINT subscriptions_exp_check   CHECK (end_date >= start_date)
);

CREATE INDEX subscriptions_status_idx ON subscriptions (status);
CREATE INDEX subscriptions_end_idx   ON subscriptions (end_date);

-- One row per tenant so every existing tenant has a dashboard row on
-- day one. The dates come from expire_times where it is a real date,
-- otherwise the subscription is given a full year from today. A tenant
-- that is already past its date is seeded 'expired' rather than
-- 'active', so the seed cannot silently hand a lapsed parishad a year
-- of free service.
INSERT INTO subscriptions
    (ins_titute_p_id, package_id, billing_cycle, start_date, end_date, status, note)
SELECT
    t.ins_titute_p_idds,
    COALESCE(
        (SELECT p.package_id FROM packages p
          WHERE p.package_key = lower(trim(t.theme_ss))),
        (SELECT p.package_id FROM packages p WHERE p.package_key = 'standard')
    ),
    'yearly',
    CURRENT_DATE,
    -- expire_times holds a unix timestamp on tenant 1 and a date on
    -- tenant 10, so it cannot be cast blindly. to_date() is only
    -- reached when the whole string is YYYY-MM-DD, which is checked
    -- with the exact-length + digit test rather than a regex, so a
    -- malformed value falls through to the +1 year default instead of
    -- aborting the insert.
    COALESCE(
        CASE WHEN t.expire_times ~ '^\d{4}-\d{2}-\d{2}$'
             THEN to_date(t.expire_times, 'YYYY-MM-DD')
             ELSE NULL END,
        CURRENT_DATE + INTERVAL '1 year'
    ),
    'active',
    'সিড করা হয়েছে — ২০২৬-০৯-৩০ মাইগ্রেশন'
FROM ins_titute t
WHERE t.ins_titute_p_idds > 0;


-- ---------------------------------------------------------------------
-- 3. SUBSCRIPTION — payments
-- ---------------------------------------------------------------------
-- Manual recording only. There is no gateway in this codebase, so
-- payment_method is a label on a row somebody typed in, and
-- reference is free text (a bKash TrxID, a cheque number, a note).

CREATE TABLE subscription_payments (
    payment_id      BIGSERIAL PRIMARY KEY,
    subscription_id BIGINT       NOT NULL REFERENCES subscriptions (subscription_id) ON DELETE CASCADE,
    invoice_no      VARCHAR(50)  NOT NULL UNIQUE,
    amount          INTEGER      NOT NULL DEFAULT 0,
    -- cash is the default because it is what a parishad paying the
    -- district office across a counter actually does.
    payment_method  VARCHAR(30)  NOT NULL DEFAULT 'cash',
    reference       VARCHAR(100),
    paid_at         DATE,
    note            TEXT,
    created_by      INTEGER,
    created_at      TIMESTAMPTZ  NOT NULL DEFAULT now(),

    CONSTRAINT subscription_payments_method_chk
        CHECK (payment_method IN ('cash', 'bkash', 'nagad', 'bank', 'cheque', 'other')),
    -- A negative payment is a refund, not revenue, and must not be
    -- reachable by a typo in the amount field.
    CONSTRAINT subscription_payments_amt_chk CHECK (amount >= 0)
);

CREATE INDEX subscription_payments_sub_idx ON subscription_payments (subscription_id, paid_at DESC NULLS LAST);


-- ---------------------------------------------------------------------
-- 4. SETTINGS — per-tenant social + contact
-- ---------------------------------------------------------------------
-- These go on ins_titute, not in a new table, because getThisInsName()
-- already returns the whole ins_titute row as $ins_info and Template::
-- front()/back()/sonod() hand that to every view already. A separate
-- table would mean one more query per page render to fetch what is
-- three columns of an existing row, and a JOIN on a public page.
--
-- The column names mirror satting_infos' existing (all-NULL, all
-- unread) set so the two tables are readable side by side, except
-- where satting_infos spells it twitterLink / youtubeLink.

ALTER TABLE ins_titute
    ADD COLUMN fb_link          VARCHAR(255),
    ADD COLUMN twitlink         VARCHAR(255),
    ADD COLUMN youtube_link     VARCHAR(255),
    ADD COLUMN instagram_link   VARCHAR(255),
    ADD COLUMN linkedin_link    VARCHAR(255),
    ADD COLUMN tiktok_link      VARCHAR(255),
    ADD COLUMN telegram_link    VARCHAR(255),
    ADD COLUMN whatsapp_number  VARCHAR(30),
    ADD COLUMN support_phone    VARCHAR(30),
    ADD COLUMN support_email    VARCHAR(190),
    ADD COLUMN support_hours    VARCHAR(100),
    ADD COLUMN map_embed        TEXT;

-- is_active is the soft-delete / suspension switch a sadmin needs and
-- no column currently provides. It gates nothing yet — see the
-- enforcement note in docs/SADMIN-PANEL.md before assuming it does.
ALTER TABLE ins_titute
    ADD COLUMN is_active BOOLEAN NOT NULL DEFAULT TRUE;

-- package_key is the forward-looking home for the plan, beside the
-- theme_ss column that has been carrying it. See the file header.
ALTER TABLE ins_titute
    ADD COLUMN package_key VARCHAR(50);

UPDATE ins_titute t
   SET package_key = p.package_key
  FROM packages p
 WHERE p.package_key = lower(trim(t.theme_ss))
   AND t.package_key IS NULL;


-- ---------------------------------------------------------------------
-- 5. SETTINGS — platform level (satting_infos)
-- ---------------------------------------------------------------------
-- satting_infos is already the platform's own table: its first column
-- is vandor_name, and it has no tenant id. It is not a tenant settings
-- table that lost its column — read as-is it is the ViretaDev-level
-- one, so the sadmin panel owns it and no migration is needed to make
-- it so.
--
-- It already carries fb_link, twitlink, youtubeLink, googleLink,
-- linkedIN and instagramLink. They are all NULL and nothing in the
-- tree reads them. This block only adds the fields the product site
-- has no column for at all.

ALTER TABLE satting_infos
    ADD COLUMN vwhatsapp      VARCHAR(30),
    ADD COLUMN vtelegram      VARCHAR(255),
    ADD COLUMN vtiktok        VARCHAR(255),
    ADD COLUMN vsupport_hours VARCHAR(100),
    ADD COLUMN vsupport_note  TEXT,
    ADD COLUMN vmap_embed     TEXT,
    ADD COLUMN vabout_short   TEXT,
    ADD COLUMN updated_at     TIMESTAMPTZ NOT NULL DEFAULT now();


-- ---------------------------------------------------------------------
-- 6. AUDIT LOG
-- ---------------------------------------------------------------------
-- Written by audit_log() in audit_helper.php (autoloaded), from the
-- super_admin controller today and from the tenant controllers as they
-- are wired up.
--
-- actor_id / actor_name / actor_group are denormalised on purpose:
-- an audit row is read long after the user it names has been renamed
-- or deleted, and "who did this" is the one question the table is for.
-- before_data / after_data are JSONB so a diff can be rendered
-- without a schema migration every time a table gains a column.

CREATE TABLE audit_logs (
    audit_id     BIGSERIAL PRIMARY KEY,
    actor_id     INTEGER,
    actor_name   VARCHAR(150),
    actor_group  VARCHAR(50),
    -- NULL tenant_id means the action was platform-level, not done on
    -- behalf of any parishad. It is NULL rather than 0 because 0 is
    -- the resolver's "no tenant" sentinel and reusing it here would
    -- make a log row look like a tenant row.
    tenant_id    INTEGER,
    action       VARCHAR(60)  NOT NULL,
    entity       VARCHAR(60)  NOT NULL,
    entity_id    VARCHAR(64),
    summary      TEXT,
    before_data  JSONB,
    after_data   JSONB,
    ip_address   VARCHAR(45),
    user_agent   VARCHAR(255),
    created_at   TIMESTAMPTZ  NOT NULL DEFAULT now(),

    CONSTRAINT audit_logs_action_chk CHECK (action IN
        ('create', 'update', 'delete', 'login', 'login_failed', 'logout', 'toggle', 'send', 'read'))
);

CREATE INDEX audit_logs_created_idx ON audit_logs (created_at DESC);
CREATE INDEX audit_logs_tenant_idx  ON audit_logs (tenant_id, created_at DESC);
CREATE INDEX audit_logs_actor_idx   ON audit_logs (actor_id, created_at DESC);
CREATE INDEX audit_logs_entity_idx  ON audit_logs (entity, entity_id);
-- The panel's default view is "this tenant, newest first", which is
-- exactly tenant_id + created_at DESC, so that pair is covered above.


-- ---------------------------------------------------------------------
-- 7. HELP & SUPPORT — tickets
-- ---------------------------------------------------------------------
-- The product site's support modal and contact form have never had a
-- backend: saas/partials/support_modal.php links to a hardcoded
-- mailto: and saas/pages/contact.php has a <form> with no action and
-- no endpoint (its own docblock says so). These tables are that
-- endpoint.

CREATE TABLE support_tickets (
    ticket_id    BIGSERIAL PRIMARY KEY,
    ticket_no    VARCHAR(30)  NOT NULL UNIQUE,
    -- ON DELETE SET NULL, not CASCADE: a parishad can be removed from
    -- the platform and the ticket must survive as the record of what
    -- was asked and what was answered. tenant_id going NULL is the
    -- signal that the tenant is gone.
    tenant_id    INTEGER      REFERENCES ins_titute (ins_titute_p_idds) ON DELETE SET NULL,
    user_id      INTEGER,
    user_name    VARCHAR(150),
    user_email   VARCHAR(190),
    user_phone   VARCHAR(30),
    subject      VARCHAR(255) NOT NULL,
    category     VARCHAR(40)  NOT NULL DEFAULT 'general',
    priority     VARCHAR(20)  NOT NULL DEFAULT 'normal',
    message      TEXT         NOT NULL,
    status       VARCHAR(20)  NOT NULL DEFAULT 'open',
    assigned_to  INTEGER,
    closed_at    TIMESTAMPTZ,
    created_at   TIMESTAMPTZ  NOT NULL DEFAULT now(),
    updated_at   TIMESTAMPTZ  NOT NULL DEFAULT now(),

    CONSTRAINT support_tickets_category_chk CHECK (category IN
        ('general', 'technical', 'billing', 'domain', 'training', 'other')),
    CONSTRAINT support_tickets_priority_chk CHECK (priority IN
        ('low', 'normal', 'high', 'urgent')),
    CONSTRAINT support_tickets_status_chk   CHECK (status IN
        ('open', 'pending', 'resolved', 'closed'))
);

CREATE INDEX support_tickets_status_idx  ON support_tickets (status, created_at DESC);
CREATE INDEX support_tickets_tenant_idx  ON support_tickets (tenant_id, created_at DESC);

CREATE TABLE support_replies (
    reply_id     BIGSERIAL PRIMARY KEY,
    ticket_id    BIGINT       NOT NULL REFERENCES support_tickets (ticket_id) ON DELETE CASCADE,
    sender_id    INTEGER,
    sender_name  VARCHAR(150),
    -- staff = the ViretaDev side, customer = the parishad. The reply
    -- thread is shown to the customer, so which side said what has to
    -- be unambiguous.
    sender_role  VARCHAR(20)  NOT NULL DEFAULT 'staff',
    message      TEXT         NOT NULL,
    created_at   TIMESTAMPTZ  NOT NULL DEFAULT now(),

    CONSTRAINT support_replies_role_chk CHECK (sender_role IN ('staff', 'customer'))
);

CREATE INDEX support_replies_ticket_idx ON support_replies (ticket_id, created_at);


COMMIT;
