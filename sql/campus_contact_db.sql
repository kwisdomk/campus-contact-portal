-- Campus contact database schema (MySQL 8 / MariaDB)
CREATE DATABASE IF NOT EXISTS campus_contact_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campus_contact_db;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(40) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL,
  message TEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_message_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_message_category FOREIGN KEY (category_id) REFERENCES categories(id),
  INDEX idx_user_created (user_id, created_at),
  INDEX idx_message_category (category_id),
  INDEX idx_message_email (email)
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (id,label) VALUES
(1,'General Inquiry'),(2,'Technical Support'),(3,'Feedback'),(4,'Complaints'),(5,'Other');

-- Optional local test account (remove before public deployment).
-- Login: sample@example.test / CampusDemo2026!
INSERT IGNORE INTO users (id, username, email, password_hash)
VALUES (1,'sample_user','sample@example.test',
'$2y$12$vgrXdsFiiqnRFnWBBd1xNu3afEiJ.y64kWBR9xS4RScu0TdlCiQc6');

-- Sample messages (import once).
INSERT INTO messages (user_id,category_id,name,email,message) VALUES
(1,1,'Test user','visitor@example.test','I would like to know how the portal works.'),
(1,2,'Test user','visitor@example.test','The first message is a test. Please confirm.'),
(1,3,'Student','student@example.test','The contact form is easy to use.');
