ALTER TABLE listings
    ADD COLUMN approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending' AFTER availability_status,
    ADD COLUMN approved_by INT NULL AFTER approval_status,
    ADD COLUMN approved_at TIMESTAMP NULL AFTER approved_by,
    ADD COLUMN admin_note TEXT NULL AFTER approved_at;

UPDATE listings
SET approval_status = 'approved',
    availability_status = 'available'
WHERE availability_status = 'available';

CREATE INDEX idx_listings_approval_status ON listings (approval_status);

