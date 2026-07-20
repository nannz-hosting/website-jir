-- Database: ff_account_store
CREATE DATABASE IF NOT EXISTS ff_account_store;
USE ff_account_store;

-- Tabel admin
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel akun_ff
CREATE TABLE akun_ff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_akun VARCHAR(100) NOT NULL,
    password_akun VARCHAR(100) NOT NULL,
    spek TEXT NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    level INT NOT NULL,
    server VARCHAR(50) NOT NULL,
    status ENUM('tersedia', 'terjual') DEFAULT 'tersedia',
    detail_extra TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel transaksi
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    akun_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(100),
    metode_pembayaran VARCHAR(50) NOT NULL,
    bukti_transfer VARCHAR(255),
    status ENUM('pending', 'verified', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (akun_id) REFERENCES akun_ff(id) ON DELETE CASCADE
);

-- Insert admin default (username: admin, password: admin123)
INSERT INTO admin (username, password_hash) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere');