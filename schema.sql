-- Safe to Spend -- shared household data store.
-- Run once via Hostinger's phpMyAdmin against the database created for this app.

CREATE TABLE app_data (
  id INT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
  data LONGTEXT NOT NULL,
  version BIGINT UNSIGNED NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  updated_by_email VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Single row, id=1: empty data means "no data yet, client should seed and push".
INSERT INTO app_data (id, data, version) VALUES (1, '', 0);

-- Previous blob is archived here before every overwrite, so a bad save or bad
-- import can be recovered from. Pruned by data.php to the most recent rows.
CREATE TABLE app_data_history (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  data LONGTEXT NOT NULL,
  version BIGINT UNSIGNED NOT NULL,
  saved_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  saved_by_email VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_history_saved_at ON app_data_history (saved_at);

-- "Stay signed in" tokens, decoupled from PHP's native session lifetime
-- (shared hosting doesn't reliably honor a longer session.gc_maxlifetime).
-- Only a SHA-256 hash of the token is stored, never the token itself.
CREATE TABLE remember_tokens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  token_hash CHAR(64) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Per-IP login attempt log, used to rate-limit api/auth.php.
CREATE TABLE auth_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(45) NOT NULL,
  attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  success TINYINT(1) NOT NULL DEFAULT 0,
  email VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE INDEX idx_auth_ip_time ON auth_attempts (ip_address, attempted_at);
