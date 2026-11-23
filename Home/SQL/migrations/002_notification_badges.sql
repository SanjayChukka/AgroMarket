ALTER TABLE notifications
    ADD COLUMN bell_seen TINYINT(1) NOT NULL DEFAULT 0,
    ADD INDEX idx_notification_badge (user_id, bell_seen, is_read);
