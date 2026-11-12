CREATE DATABASE IF NOT EXISTS savi_mare_sociale
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE savi_mare_sociale;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    username VARCHAR(50) NULL UNIQUE,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- O administrador admin/admin é criado automaticamente pelo PHP na primeira visita
-- a admin-login.php, login.php ou criar-conta.php. A senha é armazenada com hash.
