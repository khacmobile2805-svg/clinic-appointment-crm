-- ===========================================================================
--  Clinic Appointment CRM — Lược đồ CSDL (Week 6-1: "Form != Table")
--  Mọi bảng đều có id (PK) + created_at + updated_at.
--  phone dùng VARCHAR(30), tiền (fee) dùng DECIMAL(12,2), mật khẩu dùng password_hash.
--  Chạy:  mysql -u root -p < database/schema.sql
-- ===========================================================================

CREATE DATABASE IF NOT EXISTS clinic_appointment_crm
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE clinic_appointment_crm;

-- Xóa theo thứ tự an toàn để script chạy lại được nhiều lần.
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS patients;
DROP TABLE IF EXISTS users;

-- ---------------------------------------------------------------------------
--  users : tài khoản nhân viên đăng nhập cổng quản trị
-- ---------------------------------------------------------------------------
CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)  NOT NULL,
    email         VARCHAR(150)  NOT NULL,
    password_hash VARCHAR(255)  NOT NULL,          -- lưu hash từ password_hash(), không lưu mật khẩu thô
    role          ENUM('admin','staff')     NOT NULL DEFAULT 'staff',
    status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_email (email)           -- ràng buộc UNIQUE: không trùng email
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
--  patients : bệnh nhân (đăng ký công khai hoặc do nhân viên thêm)
--    UNIQUE : email   |   status: new | contacted | scheduled | inactive
-- ---------------------------------------------------------------------------
CREATE TABLE patients (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    phone       VARCHAR(30)  NULL,                  -- SĐT là chuỗi (số 0 đầu, ký tự +) -> VARCHAR
    gender      ENUM('male','female','other') NOT NULL DEFAULT 'other',
    status      ENUM('new','contacted','scheduled','inactive') NOT NULL DEFAULT 'new',
    note        TEXT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_patient_email (email),
    -- Index trên các cột hay dùng ở WHERE/ORDER BY (Week 7-4), không lạm dụng index.
    INDEX idx_patients_created_at (created_at),
    INDEX idx_patients_status_created_at (status, created_at)   -- Composite Index cho lọc theo status + sắp xếp
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
--  appointments : lịch hẹn khám của bệnh nhân
--    UNIQUE : appointment_code   |   fee DECIMAL >= 0 ; scheduled_at bắt buộc
--    status: pending | confirmed | completed | cancelled
-- ---------------------------------------------------------------------------
CREATE TABLE appointments (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    appointment_code VARCHAR(50)   NOT NULL,
    patient_name     VARCHAR(100)  NOT NULL,
    patient_email    VARCHAR(150)  NULL,
    department       VARCHAR(80)   NOT NULL DEFAULT 'General',
    scheduled_at     DATETIME      NOT NULL,
    fee              DECIMAL(12,2) NOT NULL DEFAULT 0,   -- tiền tệ dùng DECIMAL, không dùng FLOAT
    status           ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
    note             TEXT NULL,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_appointment_code (appointment_code),
    INDEX idx_appointments_created_at (created_at),
    INDEX idx_appointments_status_created_at (status, created_at),
    INDEX idx_appointments_patient_email (patient_email)
) ENGINE=InnoDB;
