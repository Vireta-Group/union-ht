-- =====================================================================
-- 20261015_platform_reviews.sql
--
-- Dynamic Multi-source Platform Reviews & Testimonials System.
-- Supports submissions from public SaaS site, citizen portal,
-- and Super Admin panel moderation.
-- =====================================================================

BEGIN;

CREATE TABLE IF NOT EXISTS platform_reviews (
    id              BIGSERIAL PRIMARY KEY,
    tenant_id       INTEGER,
    tenant_name     VARCHAR(255),
    author          VARCHAR(150) NOT NULL,
    role            VARCHAR(100) NOT NULL DEFAULT 'নাগরিক',
    service         VARCHAR(150),
    rating          SMALLINT NOT NULL DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
    impact          VARCHAR(150),
    content         TEXT NOT NULL,
    content_en      TEXT,
    avatar          VARCHAR(255),
    status          VARCHAR(20) NOT NULL DEFAULT 'pending', -- pending, approved, archived
    show_on_landing BOOLEAN NOT NULL DEFAULT false,
    featured        BOOLEAN NOT NULL DEFAULT false,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS platform_reviews_status_idx ON platform_reviews (status, show_on_landing);
CREATE INDEX IF NOT EXISTS platform_reviews_created_idx ON platform_reviews (created_at DESC);

-- Seed initial approved reviews from existing platform data
INSERT INTO platform_reviews (tenant_id, tenant_name, author, role, service, rating, impact, content, content_en, avatar, status, show_on_landing, featured)
VALUES
(1, '৪নং ধুম ইউনিয়ন পরিষদ', 'মো: খলিলুর রহমান', 'ইউপি চেয়ারম্যান', 'সনদ ও ডিজিটাল ট্রেড লাইসেন্স', 5, 'ট্যাক্স আদায় বৃদ্ধি: +১১০%', 
 'ViretaDev SaaS ব্যবহারের পর আমাদের ইউনিয়ন পরিষদের হোল্ডিং ট্যাক্স আদায় আগের বছরের চেয়ে দ্বিগুণেরও বেশি বৃদ্ধি পেয়েছে। নাগরিকরা এখন আর রসিদ বা কাগজ হারানোর ভয়ে থাকে না—সবকিছু অনলাইনে সংরক্ষিত থাকে। গ্রামের প্রতিটি বাড়ি ডিজিটাল ডাটাবেজে অন্তর্ভুক্ত হয়েছে।',
 'Turnaround time reduced by 90% with automated QR-verified certificates and double tax collection.',
 'inc/assets/himg/avatar1.png', 'approved', true, true),

(3, 'কাঞ্চন ইউনিয়ন পরিষদ', 'মোহাম্মদ শাহ আলম', 'ইউপি সচিব', 'হোল্ডিং ট্যাক্স ও অটোমেটেড রশিদ', 5, 'সনদ প্রস্তুতি সময়: মাত্র ২ মিনিট',
 'পূর্বে ওয়ারিশান ও চারিত্রিক সনদ তৈরি করতে ঘণ্টার পর ঘণ্টা পুরোনো ফাইল ও খাতা দেখতে হতো। এখন মাত্র ১ ক্লিকে কিউআর কোডসহ সনদ তৈরি হয়ে যায়। সবচেয়ে বড় বিষয়—ইউনিয়ন থেকে বের হওয়া কোনো সনদ আর কেউ নকল করতে পারে না, কিউআর স্ক্যান করলেই সত্যতা নিশ্চিত হওয়া যায়।',
 'Automated accounting and instant verification prevent fraudulent certificates.',
 'inc/assets/himg/avatar2.png', 'approved', true, true),

(4, 'বাঘমারা ইউনিয়ন পরিষদ', 'ফারহানা ইয়াসমিন', 'নাগরিক', 'ওয়ারিশান সনদপত্র', 5, 'নাগরিক সন্তুষ্টি: ৯৯%',
 'মোবাইল ফোন দিয়ে আবেদন করে পরদিনই ইউনিয়ন ডিজিটাল সেন্টার থেকে ডিজিটাল সনদ পেয়েছি। কোনো বাড়তি ঝামেলা হয়নি।',
 'Applied through mobile and got digitally signed certificate next day without waiting.',
 'inc/assets/himg/avatar3.png', 'approved', true, false),

(10, 'সালাউদ্দিন ইউনিয়ন পরিষদ', 'কাজী সালাউদ্দিন', 'ইউপি উদ্যোক্তা (UDC)', 'ডিজিটাল সেন্টার ও ট্রেড লাইসেন্স', 5, 'দৈনিক সেবা বৃদ্ধি: ৩ গুণ',
 'ইউডিসি থেকে নাগরিক সেবা প্রদান এখন অত্যন্ত দ্রুত ও সহজ। কিউআর কোড স্ক্যান করে নাগরিকরা সরাসরি সনদ যাচাই করতে পারেন। সিস্টেমের ইন্টারফেস খুবই দ্রুত এবং নিরবচ্ছিন্ন।',
 'UDC entrepreneurs can deliver authenticated civic certificates 3x faster.',
 'inc/assets/himg/avatar1.png', 'approved', true, true),

(5, 'রূপসা ইউনিয়ন পরিষদ', 'তানভীর হাসান', 'ব্যবসায়ী', 'ট্রেড লাইসেন্স রিনিউয়াল', 5, 'রিনিউ সময়: মাত্র ১৫ মিনিট',
 'গত বছর লাইসেন্স রিনিউ করতে ৩ দিন লেগেছিল, এবার মাত্র ১৫ মিনিটে অনলাইনে রিনিউ সম্পন্ন হয়েছে। অসাধারণ সার্ভিস!',
 'Instant online renewal of commercial licenses in minutes instead of days.',
 'inc/assets/himg/avatar2.png', 'approved', true, true);

COMMIT;
