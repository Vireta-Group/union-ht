-- =====================================================================
-- Migration: 20261028_core_performance_indexes.sql
--
-- Performance & Latency Optimization:
-- Adds B-Tree compound indexes across high-traffic tables:
-- family_data_sheet, family_member, certificate_person_verify_info_editing,
-- sonod_genarates, collect_amnt_invoice, and users.
-- Prevents full table scans on large parishad datasets.
-- =====================================================================

BEGIN;

-- 1. Family Data Sheet Indexes
CREATE INDEX IF NOT EXISTS idx_fds_tenant_holding ON family_data_sheet(ins_aut_iiid, holding_number);
CREATE INDEX IF NOT EXISTS idx_fds_tenant_ward ON family_data_sheet(ins_aut_iiid, word_number);
CREATE INDEX IF NOT EXISTS idx_fds_tenant_nid ON family_data_sheet(ins_aut_iiid, nid_number);
CREATE INDEX IF NOT EXISTS idx_fds_uniqid ON family_data_sheet("uniqID");
CREATE INDEX IF NOT EXISTS idx_fds_mobile ON family_data_sheet(ins_aut_iiid, mobile_number_one);

-- 2. Family Member Indexes
CREATE INDEX IF NOT EXISTS idx_fm_datasid ON family_member(family_datasid);
CREATE INDEX IF NOT EXISTS idx_fm_tenant_nid ON family_member(in_s_pr_id_dd, nid_bdris);
CREATE INDEX IF NOT EXISTS idx_fm_tenant_name ON family_member(in_s_pr_id_dd, member_name);

-- 3. Certificate Person Verify Indexes
CREATE INDEX IF NOT EXISTS idx_cpvie_sonod_uniqs ON certificate_person_verify_info_editing(ver_per_info_sonod_uniqs);
CREATE INDEX IF NOT EXISTS idx_cpvie_ward ON certificate_person_verify_info_editing(ver_per_info_ward_no);

-- 4. Sonod Generates Indexes
CREATE INDEX IF NOT EXISTS idx_sg_tenant_uniqs ON sonod_genarates(insididid_pridididid, sonod_uniqs);
CREATE INDEX IF NOT EXISTS idx_sg_global_uid ON sonod_genarates(sonod_global_verify_uids);

-- 5. Tax Collection & Invoice Indexes
CREATE INDEX IF NOT EXISTS idx_cai_tenant_date ON collect_amnt_invoice(ins_ti_tute_pridd, date_s_today_s);
CREATE INDEX IF NOT EXISTS idx_cai_family_id ON collect_amnt_invoice(family_data_id_id_dis);
CREATE INDEX IF NOT EXISTS idx_cai_invoice_no ON collect_amnt_invoice(invoice_no_s);

-- 6. Users Table Indexes
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_utype ON users(u_type);
CREATE INDEX IF NOT EXISTS idx_users_ins_pr_id ON users(ins_pr_id_s);

COMMIT;
