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

1. Tao database va import schema + du lieu mau:

   ```bash
   mysql -u root -p -e "CREATE DATABASE batdongsan CHARACTER SET utf8mb4;"
   mysql -u root -p batdongsan < database/schema.sql
   mysql -u root -p batdongsan < database/seed.sql
   ```

2. Cau hinh ket noi database bang bien moi truong (hoac sua truc tiep gia tri mac dinh trong
   `config/database.php`):

   | Bien        | Mo ta                         | Mac dinh    |
   |-------------|--------------------------------|-------------|
   | `DB_HOST`   | Host MySQL                     | `127.0.0.1` |
   | `DB_PORT`   | Port MySQL                     | `3306`      |
   | `DB_NAME`   | Ten database                   | `batdongsan`|
   | `DB_USER`   | User database                  | `root`      |
   | `DB_PASS`   | Mat khau database              | (rong)      |
   | `APP_URL`   | Base URL neu chay duoi subfolder | (rong)    |
   | `APP_DEBUG` | Bat hien thi loi chi tiet (`1`) | tat        |

3. Dam bao thu muc `uploads/properties/` co quyen ghi (`chmod 755` hoac tuong duong) de upload hinh anh
   bat dong san.

4. Chay thu bang PHP built-in server:

   ```bash
   DB_USER=root DB_PASS=your_password php -S localhost:8000
   ```

   Roi mo `http://localhost:8000/index.php`.

   Voi may chu Apache/Nginx, tro document root vao thu muc goc cua du an.

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
