-- Homeland Real Estate - Sample seed data
USE batdongsan;

-- Admin mac dinh: email admin@homeland.vn / mat khau: Admin@123
INSERT INTO users (full_name, email, password_hash, role, phone, zalo, facebook, is_active) VALUES
('Quan Tri Vien', 'admin@homeland.vn', '$2y$12$VnULYNBi5tFnbXqSJnANwOmI0YS/6F32D1QUj6KwUsGxHtWYh343m', 'admin', '0900000000', '0900000000', 'https://facebook.com/homeland.admin', 1);
-- Mat khau da hash o tren la 'Admin@123'

-- Nhan vien mau: mat khau: Nhanvien@123
INSERT INTO users (full_name, email, password_hash, role, phone, zalo, facebook, is_active) VALUES
('Nguyen Van An', 'an.nguyen@homeland.vn', '$2y$12$.YMqLBsdQbVHfajPZRUt5.LVZuEx.9ZvvV/hSFJwepzFVpswmYqkK', 'employee', '0901234567', '0901234567', 'https://facebook.com/an.nguyen', 1),
('Tran Thi Binh', 'binh.tran@homeland.vn', '$2y$12$.YMqLBsdQbVHfajPZRUt5.LVZuEx.9ZvvV/hSFJwepzFVpswmYqkK', 'employee', '0912345678', '0912345678', 'https://facebook.com/binh.tran', 1),
('Le Minh Cuong', 'cuong.le@homeland.vn', '$2y$12$.YMqLBsdQbVHfajPZRUt5.LVZuEx.9ZvvV/hSFJwepzFVpswmYqkK', 'employee', '0923456789', '0923456789', 'https://facebook.com/cuong.le', 1);
-- Mat khau da hash o tren la 'Nhanvien@123'

-- Thanh pho
INSERT INTO cities (id, name) VALUES
(1, 'Ha Noi'),
(2, 'TP. Ho Chi Minh'),
(3, 'Da Nang');

-- Quan / Huyen
INSERT INTO districts (city_id, name) VALUES
(1, 'Quan Ba Dinh'),
(1, 'Quan Cau Giay'),
(1, 'Quan Hai Ba Trung'),
(1, 'Quan Dong Da'),
(2, 'Quan 1'),
(2, 'Quan 3'),
(2, 'Quan 7'),
(2, 'Quan Binh Thanh'),
(3, 'Quan Hai Chau'),
(3, 'Quan Son Tra');

-- Loai bat dong san
INSERT INTO property_types (id, name, slug, icon) VALUES
(1, 'Chung cu', 'chung-cu', 'building'),
(2, 'Nha rieng', 'nha-rieng', 'home'),
(3, 'Van phong', 'van-phong', 'briefcase'),
(4, 'Mat bang kinh doanh', 'mat-bang-kinh-doanh', 'store');

-- Bat dong san mau
INSERT INTO properties (title, slug, description, property_type_id, transaction_type, price, price_unit, city_id, district_id, address, area, bedrooms, bathrooms, floors, status, employee_id) VALUES
('Biet thu sang trong tai Ba Dinh', 'biet-thu-sang-trong-tai-ba-dinh', 'Biet thu 4 phong ngu, ho boi rieng, san vuon rong rai, noi that cao cap.', 2, 'sale', 12500000000, 'total', 1, 1, '123 Doi Can, Ba Dinh', 250, 4, 3, 3, 'available', 2),
('Can ho hien dai Quan 7', 'can-ho-hien-dai-quan-7', 'Can ho 2 phong ngu view song, day du noi that, an ninh 24/7.', 1, 'rent', 12000000, 'month', 2, 7, '456 Nguyen Van Linh, Quan 7', 85, 2, 2, 1, 'available', 3),
('Van phong cho thue Quan 1', 'van-phong-cho-thue-quan-1', 'Van phong hang A, trung tam Quan 1, view dep, day du tien ich.', 3, 'rent', 35000000, 'month', 2, 5, '789 Le Loi, Quan 1', 200, 0, 2, 1, 'available', 3),
('Nha pho Cau Giay 5 tang', 'nha-pho-cau-giay-5-tang', 'Nha moi xay, 5 tang, thang may, gara oto, khu vuc an ninh.', 2, 'sale', 8900000000, 'total', 1, 2, '12 Xuan Thuy, Cau Giay', 100, 5, 5, 5, 'available', 2),
('Mat bang kinh doanh Hai Chau', 'mat-bang-kinh-doanh-hai-chau', 'Mat tien duong lon, phu hop kinh doanh cafe, showroom.', 4, 'rent', 25000000, 'month', 3, 9, '99 Tran Phu, Hai Chau', 150, 0, 1, 2, 'available', 4),
('Chung cu cao cap Binh Thanh', 'chung-cu-cao-cap-binh-thanh', 'Can ho 3 phong ngu, noi that day du, tien ich hoan hao.', 1, 'sale', 4200000000, 'total', 2, 8, '234 Dien Bien Phu, Binh Thanh', 110, 3, 2, 1, 'available', 4);

-- Anh dai dien (placeholder)
INSERT INTO property_images (property_id, image_path, is_primary) VALUES
(1, 'assets/images/sample-house-1.svg', 1),
(2, 'assets/images/sample-apartment-1.svg', 1),
(3, 'assets/images/sample-office-1.svg', 1),
(4, 'assets/images/sample-house-2.svg', 1),
(5, 'assets/images/sample-retail-1.svg', 1),
(6, 'assets/images/sample-apartment-2.svg', 1);

-- Tien ich / dich vu
INSERT INTO property_amenities (property_id, name) VALUES
(1, 'Ho boi'), (1, 'San vuon'), (1, 'Gara oto'), (1, 'Bao ve 24/7'),
(2, 'Gym'), (2, 'Ho boi'), (2, 'Bao ve 24/7'), (2, 'Thang may'),
(3, 'Thang may'), (3, 'May lanh trung tam'), (3, 'Cho do xe'),
(4, 'Gara oto'), (4, 'Thang may'), (4, 'Camera an ninh'),
(5, 'Cho do xe'), (5, 'Camera an ninh'),
(6, 'Gym'), (6, 'Ho boi'), (6, 'Cong vien noi khu'), (6, 'Bao ve 24/7');
