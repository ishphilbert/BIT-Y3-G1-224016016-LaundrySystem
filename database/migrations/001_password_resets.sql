USE online_laundry_db;

CREATE TABLE IF NOT EXISTS password_resets (
    reset_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_password_resets_user (user_id),
    INDEX idx_password_resets_expiry (expires_at)
) ENGINE=InnoDB;
