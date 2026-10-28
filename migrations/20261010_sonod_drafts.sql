-- Migration: sonod_drafts table for draft certificates and auto-save
CREATE TABLE IF NOT EXISTS sonod_drafts (
    id SERIAL PRIMARY KEY,
    ins_id INT NOT NULL,
    user_id INT NOT NULL,
    step_number INT DEFAULT 1,
    draft_title VARCHAR(255),
    applicant_name VARCHAR(255),
    applicant_nid_brn VARCHAR(50),
    sonod_type_id VARCHAR(50),
    payload_json TEXT NOT NULL,
    created_at INT NOT NULL,
    updated_at INT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_sonod_drafts_ins ON sonod_drafts(ins_id, updated_at DESC);
