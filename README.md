# Homeland Real Estate

Website tim kiem bat dong san viet bang PHP thuan (khong framework) + MySQL. Ho tro tim kiem theo khu vuc
(tinh/thanh - quan/huyen), loai bat dong san (chung cu, nha rieng, van phong, mat bang kinh doanh), va loai
giao dich (mua ban / cho thue). Moi tin dang co the duoc gan cho mot nhan vien phu trach; trang chi tiet
hien thi so tang, so phong, tien ich va 3 nut lien he nhanh voi nhan vien (Dien thoai, Zalo, Facebook).

He thong co phan quyen 2 vai tro:
- **Admin (Quan tri vien)**: quan ly toan bo nhan vien va toan bo bat dong san, gan bat dong san cho nhan vien.
- **Employee (Nhan vien)**: chi quan ly (them/sua/xoa) cac bat dong san duoc phan cong cho minh.

## Yeu cau he thong

- PHP >= 8.0 voi extension `pdo_mysql`
- MySQL / MariaDB >= 10.4

## Cai dat

Gia tri mac dinh trong `config/database.php` da khop san voi cau hinh MySQL cua **XAMPP**
(host `localhost`, port `3306`, user `root`, khong mat khau) nen thong thuong khong can sua gi them.

1. Copy thu muc du an vao `htdocs` cua XAMPP, vi du: `C:\xampp\htdocs\batdongsan` (Windows) hoac
   `/Applications/XAMPP/htdocs/batdongsan` (macOS).

2. Khoi dong **Apache** va **MySQL** trong XAMPP Control Panel.

3. Tao database va import schema + du lieu mau — co the dung phpMyAdmin
   (`http://localhost/phpmyadmin`, tab Import) hoac dong lenh:

   ```bash
   mysql -u root -e "CREATE DATABASE batdongsan CHARACTER SET utf8mb4;"
   mysql -u root batdongsan < database/schema.sql
   mysql -u root batdongsan < database/seed.sql
   ```

4. Neu MySQL cua ban co dat mat khau cho `root`, hoac dung host/port/user khac, cau hinh lai qua bien
   moi truong (hoac sua truc tiep gia tri mac dinh trong `config/database.php`):

   | Bien        | Mo ta                         | Mac dinh    |
   |-------------|--------------------------------|-------------|
   | `DB_HOST`   | Host MySQL                     | `localhost` |
   | `DB_PORT`   | Port MySQL                     | `3306`      |
   | `DB_NAME`   | Ten database                   | `batdongsan`|
   | `DB_USER`   | User database                  | `root`      |
   | `DB_PASS`   | Mat khau database              | (rong)      |
   | `APP_URL`   | Base URL neu chay duoi subfolder | (rong)    |
   | `APP_DEBUG` | Bat hien thi loi chi tiet (`1`) | tat        |

5. Dam bao thu muc `uploads/properties/` co quyen ghi de upload hinh anh bat dong san.

6. Mo trinh duyet vao `http://localhost/batdongsan/index.php`.

   (Co the chay thu nhanh bang PHP built-in server thay vi XAMPP: `php -S localhost:8000`, roi mo
   `http://localhost:8000/index.php`.)

## Tai khoan demo (tu file `database/seed.sql`)

| Vai tro   | Email                     | Mat khau       |
|-----------|---------------------------|----------------|
| Admin     | admin@homeland.vn         | `Admin@123`    |
| Nhan vien | an.nguyen@homeland.vn     | `Nhanvien@123` |
| Nhan vien | binh.tran@homeland.vn     | `Nhanvien@123` |
| Nhan vien | cuong.le@homeland.vn      | `Nhanvien@123` |

## Cau truc thu muc

```
config/            Cau hinh database + hang so he thong
includes/           Header, footer, cac ham tien ich dung chung, the property-card
database/           schema.sql (cau truc bang) va seed.sql (du lieu mau)
assets/             CSS, JS, hinh anh placeholder
uploads/properties/ Noi luu hinh anh bat dong san do nguoi dung upload
admin/               Khu vuc quan tri (dashboard, quan ly nhan vien / bat dong san / ho so)
index.php            Trang chu
properties.php        Trang tim kiem / danh sach bat dong san
property-detail.php   Trang chi tiet bat dong san
login.php / logout.php Dang nhap / dang xuat
```

## Tinh nang chinh

- Tim kiem va loc bat dong san theo tinh/thanh, quan/huyen, loai hinh, loai giao dich (mua/thue), muc gia,
  so phong ngu.
- Trang chi tiet hien thi day du: dien tich, so phong ngu, so phong tam, so tang, tien ich/dich vu, va
  the lien he nhan vien phu trach voi 3 icon: Dien thoai (`tel:`), Zalo (`https://zalo.me/<sdt>`), Facebook.
- Dashboard quan tri:
  - Admin: xem thong ke tong quan, quan ly nhan vien (them/sua/xoa, dat lai mat khau), quan ly toan bo
    bat dong san va gan/doi nhan vien phu trach cho tung tin.
  - Nhan vien: xem thong ke rieng, chi quan ly cac bat dong san duoc phan cong (them tin moi se tu dong
    gan cho chinh minh), khong the xem/sua tin cua nhan vien khac.
  - Ho so ca nhan de tu cap nhat so dien thoai, Zalo, link Facebook (hien thi truc tiep o trang chi tiet
    BDS) va doi mat khau.
- Upload nhieu hinh anh cho moi tin dang, chon anh de xoa khi chinh sua.
