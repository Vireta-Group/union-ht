-- Migration: 20261027_sonod_address_standardization.sql
-- Adds discrete administrative geography address fields:
-- Post Office (ডাকঘর), Upazila (উপজেলা), District (জেলা), Union (ইউনিয়ন পরিষদ)

ALTER TABLE certificate_person_verify_info_editing 
    ADD COLUMN IF NOT EXISTS post_office character varying(150),
    ADD COLUMN IF NOT EXISTS upazila_name character varying(150),
    ADD COLUMN IF NOT EXISTS district_name character varying(150),
    ADD COLUMN IF NOT EXISTS union_name character varying(150);

ALTER TABLE sonod_drafts
    ADD COLUMN IF NOT EXISTS post_office character varying(150),
    ADD COLUMN IF NOT EXISTS upazila_name character varying(150),
    ADD COLUMN IF NOT EXISTS district_name character varying(150),
    ADD COLUMN IF NOT EXISTS union_name character varying(150);
