-- Homeland Real Estate - Database schema
-- Charset utf8mb4 to support Vietnamese text

CREATE DATABASE IF NOT EXISTS batdongsan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE batdongsan;

-- ---------------------------------------------------------------------
-- Users: admin quan ly he thong, employee (nhan vien) quan ly nha rieng
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
    phone VARCHAR(30) DEFAULT NULL,
    zalo VARCHAR(150) DEFAULT NULL COMMENT 'So dien thoai hoac link Zalo',
    facebook VARCHAR(255) DEFAULT NULL COMMENT 'Link Facebook ca nhan',
    avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Dia diem: Thanh pho / Tinh -> Quan / Huyen
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS districts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Loai bat dong san: Chung cu, Nha rieng, Van phong, Mat bang kinh doanh
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS property_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Bat dong san
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    description TEXT,
    property_type_id INT NOT NULL,
    transaction_type ENUM('sale', 'rent') NOT NULL DEFAULT 'sale' COMMENT 'sale=Mua ban, rent=Cho thue',
    price DECIMAL(15,2) NOT NULL DEFAULT 0,
    price_unit ENUM('total', 'month') NOT NULL DEFAULT 'total' COMMENT 'total=tong gia, month=gia/thang',
    city_id INT NOT NULL,
    district_id INT NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    area DECIMAL(10,2) DEFAULT NULL COMMENT 'Dien tich m2',
    bedrooms INT DEFAULT 0,
    bathrooms INT DEFAULT 0,
    floors INT DEFAULT 1,
    status ENUM('available', 'pending', 'sold', 'rented') NOT NULL DEFAULT 'available',
    employee_id INT DEFAULT NULL COMMENT 'Nhan vien phu trach',
    views INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_type_id) REFERENCES property_types(id),
    FOREIGN KEY (city_id) REFERENCES cities(id),
    FOREIGN KEY (district_id) REFERENCES districts(id),
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS property_amenities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_properties_search ON properties (city_id, district_id, property_type_id, transaction_type, status);
