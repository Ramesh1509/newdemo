CREATE DATABASE IF NOT EXISTS jobs_portal;
USE jobs_portal;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(150),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  section VARCHAR(50),
  title VARCHAR(255),
  url VARCHAR(500),
  is_new TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/* Optional: insert a default admin with plaintext password 'adminpass' .
   (PHP will upgrade it to hashed on first successful login.)
*/
INSERT INTO admins (username, password, email) VALUES ('admin', 'adminpass', '');