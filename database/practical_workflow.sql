USE university_complaint_system;
ALTER TABLE complaints ADD COLUMN IF NOT EXISTS next_update DATE NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified_at DATETIME NULL;
