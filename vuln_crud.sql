CREATE DATABASE IF NOT EXISTS vuln_crud;
USE vuln_crud;

DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password_plain VARCHAR(255) NOT NULL,
  password_hash VARCHAR(255) NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0
);

INSERT INTO users (username, password_plain, password_hash, is_admin) VALUES
('alice', 'alice123', NULL, 0),
('bob',   'bob123',   NULL, 0),
('admin', 'admin123', NULL, 1);
