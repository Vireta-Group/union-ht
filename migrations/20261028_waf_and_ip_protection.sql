-- Migration: 20261028_waf_and_ip_protection.sql
-- Web Application Firewall (WAF), IP Ban, and Security Throttle System.

CREATE TABLE IF NOT EXISTS banned_ips (
    id SERIAL PRIMARY KEY,
    ip_address VARCHAR(45) UNIQUE NOT NULL,
    reason TEXT,
    banned_by VARCHAR(100) DEFAULT 'WAF Auto-Ban',
    expires_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_banned_ips_ip ON banned_ips(ip_address);
