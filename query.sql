CREATE DATABASE issue_management_db;

USE issue_management_db;

-- Set the timezone to UTC
SET
    TIME_ZONE = '+00:00';

CREATE TABLE
    IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        role ENUM ('ADMIN', 'USER') DEFAULT 'USER',
        name VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

CREATE TABLE
    IF NOT EXISTS issues (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        status ENUM ('OPEN', 'IN_PROGRESS', 'RESOLVED') DEFAULT 'OPEN',
        priority ENUM ('LOW', 'MEDIUM', 'HIGH') DEFAULT 'LOW',
        assignee_id BIGINT UNSIGNED DEFAULT NULL,
        reporter_id BIGINT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_assignee_id FOREIGN KEY (assignee_id) REFERENCES users (id) ON DELETE SET NULL,
        CONSTRAINT fk_reporter_id FOREIGN KEY (reporter_id) REFERENCES users (id) ON DELETE CASCADE
    );

CREATE TABLE
    IF NOT EXISTS user_tokens (
        id SERIAL PRIMARY KEY,
        selector CHAR(32) NOT NULL,
        hashed_validator CHAR(255) NOT NULL,
        expiry DATETIME NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    );

CREATE TABLE
    IF NOT EXISTS sessions (
        id VARCHAR(128) PRIMARY KEY,
        data TEXT NOT NULL,
        last_access TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

ALTER TABLE issues
ADD COLUMN is_deleted TINYINT (1) DEFAULT 0;

CREATE TABLE
    IF NOT EXISTS issue_deletion_logs (
        id SERIAL PRIMARY KEY,
        issue_id BIGINT UNSIGNED NOT NULL,
        deleted_by BIGINT UNSIGNED NOT NULL,
        deleted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_issue_id FOREIGN Key (issue_id) REFERENCES issues (id) ON DELETE CASCADE,
        CONSTRAINT fk_deleted_by FOREIGN KEY (deleted_by) REFERENCES users (id) ON DELETE CASCADE
    );

ALTER TABLE issue_deletion_logs
RENAME COLUMN id TO log_id;