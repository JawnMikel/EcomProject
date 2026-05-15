-- Add 2FA Verification Codes Table

CREATE TABLE IF NOT EXISTS two_factor_codes (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT            NOT NULL,
    code         VARCHAR(6)     NOT NULL,
    expires_at   DATETIME       NOT NULL,
    attempts     INT            DEFAULT 0,
    max_attempts INT            DEFAULT 5,
    used         TINYINT(1)     DEFAULT 0,
    created_at   DATETIME       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_expires (user_id, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: Add email settings to .env (see setup)
-- EMAIL_FROM=noreply@gainz-app.local
-- EMAIL_SMTP_HOST=localhost
-- EMAIL_SMTP_PORT=1025
