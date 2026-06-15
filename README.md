# Clinic Appointment CRM

Secure Mini CRM (PHP, MVC) — quản lý **Bệnh nhân** (Patients) và **Lịch hẹn** (Appointments)
cho một phòng khám. Bài Lab06 Final: Front Controller + Router, form an toàn
(validate server-side / honeypot / rate limit / CSRF / PRG), session login, PDO + prepared
statements, CRUD + search / pagination / sort whitelist, kiến trúc MVC 4 tầng, lỗi production-safe.

## Yêu cầu môi trường
- PHP >= 8.1 (CLI) có extension **pdo_mysql** và **mbstring**
  (`php -m | findstr /R "pdo_mysql mbstring"` trên Windows, hoặc `php -m | grep -E "pdo_mysql|mbstring"`)
- MySQL >= 8.0 hoặc MariaDB
- Git

## Cấu hình
Copy `config/database.php.example` thành `config/database.php` rồi điền mật khẩu MySQL của bạn.
`config/database.php` đã nằm trong `.gitignore` để không lộ mật khẩu khi push GitHub.

## Tạo database
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql        # 2 user + 24 bệnh nhân + 24 lịch hẹn
# (tùy chọn) seed_bulk.sql ~300 dòng/bảng để thử pagination / EXPLAIN:
# mysql -u root -p < database/seed_bulk.sql
```
> MySQL Workbench bật "Safe Update" có thể chặn lệnh DELETE — `seed.sql` đã có sẵn
> `SET SQL_SAFE_UPDATES = 0;` ở đầu file nên import qua Workbench không bị Error 1175.

## Chạy server
```bash
php -S localhost:8000 -t public
```
Mở http://localhost:8000 — mọi request đi qua `public/index.php` (Front Controller).
Kiểm tra nhanh: http://localhost:8000/health phải trả `"database":"up"`.

## Tài khoản demo (mật khẩu: `123456`)
| Email | Vai trò |
|-------|---------|
| admin@clinic.test | admin |
| staff@clinic.test | staff |

## Danh sách route
| Method | URL | Controller@Action | Ý nghĩa |
|---|---|---|---|
| GET | / | HomeController@index | Trang chủ / redirect dashboard nếu đã login |
| GET | /login | AuthController@login | Form đăng nhập |
| POST | /login | AuthController@handleLogin | Validate + verify + regenerate id + redirect |
| POST | /logout | AuthController@logout | Logout sạch (destroy_session) |
| GET | /dashboard | DashboardController@index | Tổng quan (yêu cầu login) |
| GET | /registrations/create | RegistrationController@create | Form công khai đăng ký khám |
| POST | /registrations | RegistrationController@store | Honeypot + rate limit + validate + PRG |
| GET | /patients | PatientController@index | List + search + pagination + sort whitelist |
| GET | /patients/create | PatientController@create | Form thêm bệnh nhân |
| POST | /patients/store | PatientController@store | Validate + create + duplicate + PRG |
| GET | /patients/edit?id= | PatientController@edit | Form sửa theo id |
| POST | /patients/update | PatientController@update | Validate + update (bỏ qua id khi check trùng) + PRG |
| POST | /patients/delete | PatientController@delete | Xóa bằng POST + PRG |
| GET | /appointments | AppointmentController@index | List + search + pagination + sort whitelist |
| GET | /appointments/create | AppointmentController@create | Form tạo lịch hẹn |
| POST | /appointments/store | AppointmentController@store | Validate + create + duplicate code + PRG |
| GET | /appointments/edit?id= | AppointmentController@edit | Form sửa theo id |
| POST | /appointments/update | AppointmentController@update | Validate + update + PRG |
| POST | /appointments/delete | AppointmentController@delete | Xóa bằng POST + PRG |
| GET | /health | HealthController@index | JSON kiểm tra app/db |

Trang lỗi: **404** (URL không tồn tại), **405** (sai method), **419** (CSRF token sai/thiếu),
**500** (lỗi hệ thống — production che chi tiết).

## Cấu trúc thư mục
```
public/index.php          Front Controller (entry point duy nhất) + assets/app.css
config/                   app.php (debug/timezone/session_timeout/rate_limit_secs)
                          database.php (gitignored) + database.php.example
app/Core/                 Database, Router, helpers, DuplicateRecordException
app/Controllers/          Home, Health, Auth, Dashboard, Patient, Registration, Appointment
app/Services/             Auth, Dashboard, Patient, Appointment (validate, nghiệp vụ)
app/Repositories/         User, Dashboard, Patient, Appointment (SQL prepared statements)
app/Views/                layouts, partials, auth, dashboard, patients, appointments,
                          registration, errors, home
database/                 schema.sql, seed.sql, seed_bulk.sql
storage/logs/             app.log (lỗi DB / hệ thống)
```

## Bảo mật & chất lượng
- 100% truy vấn dùng **prepared statements** (PDO, `EMULATE_PREPARES=false`, `charset=utf8mb4`).
- Mọi output trong View bọc `e()` (htmlspecialchars) chống XSS.
- **CSRF token** trên mọi form POST; thiếu/sai token trả **419**.
- `session_regenerate_id(true)` sau login; cookie **HttpOnly + SameSite=Lax + Secure**;
  logout và idle-timeout dùng chung `destroy_session()` (xóa biến + cookie + hủy phiên).
- Sort/direction qua **whitelist** PHP — input nguy hiểm rơi về giá trị mặc định.
- **UNIQUE constraint** chặn trùng `email` / `appointment_code`; bắt lỗi 1062 thành thông báo
  thân thiện; khi Update bỏ qua id hiện tại (`id <> :ignore_id`).
- Tiền dùng `DECIMAL(12,2)`; phí khám nhập có dấu chấm ngăn cách được chuẩn hóa ở server.

## Debug / Production
`config/app.php` → đặt `'debug' => false` ở production. Khi đó trang lỗi 500 **không** lộ
SQLSTATE / tên bảng / đường dẫn / stack trace; lỗi thật được ghi vào `storage/logs/app.log`.

## Khắc phục sự cố
- **`/health` báo `database: down` / 500**: thiếu `pdo_mysql`, hoặc sai thông tin trong `config/database.php`.
- **`Call to undefined function mb_strlen()`**: thiếu extension `mbstring`.
- **Access denied (using password: NO)**: chưa điền mật khẩu vào `config/database.php`.
- **Workbench Error 1175 khi import**: do Safe Update — `seed.sql` đã tự tắt bằng `SET SQL_SAFE_UPDATES = 0;`.