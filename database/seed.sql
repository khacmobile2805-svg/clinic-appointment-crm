-- ===========================================================================
--  Clinic Appointment CRM — Seed data
--  Demo password for ALL users below is: 123456
--  Run AFTER schema.sql:  mysql -u root -p < database/seed.sql
-- ===========================================================================

USE clinic_appointment_crm;

-- Tắt safe-update mode để các lệnh DELETE (làm seed chạy lại được) không bị chặn trên MySQL Workbench.
SET SQL_SAFE_UPDATES = 0;

DELETE FROM appointments;
DELETE FROM patients;
DELETE FROM users;
ALTER TABLE appointments AUTO_INCREMENT = 1;
ALTER TABLE patients     AUTO_INCREMENT = 1;
ALTER TABLE users        AUTO_INCREMENT = 1;

-- ---- users (login accounts) ----------------------------------------------
INSERT INTO users (name, email, password_hash, role, status) VALUES
  ('Admin Clinic', 'admin@clinic.test', '$2y$10$OKxrhkW8rhtpo3dltnb3ouBQVBKZyHZmxU91PxUnlXj81/qUNLodi', 'admin', 'active'),
  ('Staff Reception', 'staff@clinic.test', '$2y$10$OKxrhkW8rhtpo3dltnb3ouBQVBKZyHZmxU91PxUnlXj81/qUNLodi', 'staff', 'active');

-- ---- patients (24 rows for pagination) -----------------------------------
INSERT INTO patients (full_name, email, phone, gender, status, note) VALUES
  ('Thu Tran', 'thu.tran0@example.com', '0904332181', 'other', 'inactive', 'Quan tam kham tong quat'),
  ('Anh Tran', 'anh.tran1@example.com', '0933890838', 'female', 'contacted', 'Hen tai kham'),
  ('Son Hoang', 'son.hoang2@example.com', '0902654235', 'male', 'new', 'Hen tai kham'),
  ('Dung Vu', 'dung.vu3@example.com', '0959407816', 'male', 'scheduled', 'Can dat lich som'),
  ('Thao Phan', 'thao.phan4@example.com', '0959310341', 'male', 'new', 'Hen tai kham'),
  ('Lan Bui', 'lan.bui5@example.com', '0952553419', 'other', 'contacted', 'Khach moi'),
  ('Vy Pham', 'vy.pham6@example.com', '0927648350', 'male', 'new', 'Da goi lan 1'),
  ('Nam Hoang', 'nam.hoang7@example.com', '0913953767', 'male', 'scheduled', 'Tu van qua hotline'),
  ('Khanh Duong', 'khanh.duong8@example.com', '0988496965', 'male', 'contacted', 'Khach moi'),
  ('Phong Tran', 'phong.tran9@example.com', '0901226916', 'female', 'inactive', 'Khach moi'),
  ('Lan Do', 'lan.do10@example.com', '0901845146', 'male', 'inactive', 'Quan tam kham tong quat'),
  ('Vy Duong', 'vy.duong11@example.com', '0948281489', 'male', 'contacted', 'Da goi lan 1'),
  ('Yen Le', 'yen.le12@example.com', '0988095701', 'female', 'scheduled', 'Tu van qua hotline'),
  ('Bao Pham', 'bao.pham13@example.com', '0991171822', 'other', 'inactive', 'Khach moi'),
  ('Ha Hoang', 'ha.hoang14@example.com', '0989638346', 'other', 'scheduled', 'Hen tai kham'),
  ('Loan Do', 'loan.do15@example.com', '0971331509', 'other', 'contacted', 'Khach moi'),
  ('Khanh Nguyen', 'khanh.nguyen16@example.com', '0910310518', 'male', 'scheduled', 'Can dat lich som'),
  ('Phong Pham', 'phong.pham17@example.com', '0982997376', 'male', 'new', 'Quan tam kham tong quat'),
  ('Trang Dang', 'trang.dang18@example.com', '0956670106', 'other', 'scheduled', 'Quan tam kham tong quat'),
  ('Khanh Pham', 'khanh.pham19@example.com', '0938726247', 'male', 'new', 'Hen tai kham'),
  ('Dat Phan', 'dat.phan20@example.com', '0981080132', 'female', 'inactive', 'Hen tai kham'),
  ('Hieu Phan', 'hieu.phan21@example.com', '0960260647', 'female', 'inactive', 'Can dat lich som'),
  ('Vy Ly', 'vy.ly22@example.com', '0987234309', 'other', 'new', 'Can dat lich som'),
  ('Mai Nguyen', 'mai.nguyen23@example.com', '0909788208', 'male', 'contacted', 'Quan tam kham tong quat');

-- ---- appointments (24 rows; unique appointment_code) ---------------------
INSERT INTO appointments (appointment_code, patient_name, patient_email, department, scheduled_at, fee, status, note) VALUES
  ('APT-2026-0001', 'Khanh Pham', 'khanh.pham19@example.com', 'General', '2026-07-13 09:00:00', 300000.00, 'pending', 'Uu tien buoi sang'),
  ('APT-2026-0002', 'Son Hoang', 'son.hoang2@example.com', 'Pediatrics', '2026-08-09 11:00:00', 500000.00, 'confirmed', 'Co bao hiem'),
  ('APT-2026-0003', 'Yen Le', 'yen.le12@example.com', 'Cardiology', '2026-08-15 14:00:00', 200000.00, 'pending', 'Thanh toan tien mat'),
  ('APT-2026-0004', 'Khanh Pham', 'khanh.pham19@example.com', 'Dentistry', '2026-06-03 11:00:00', 1500000.00, 'completed', 'Tai kham'),
  ('APT-2026-0005', 'Vy Duong', 'vy.duong11@example.com', 'General', '2026-07-12 13:00:00', 250000.00, 'cancelled', 'Uu tien buoi sang'),
  ('APT-2026-0006', 'Vy Ly', 'vy.ly22@example.com', 'Dermatology', '2026-06-22 13:00:00', 200000.00, 'confirmed', 'Co bao hiem'),
  ('APT-2026-0007', 'Dung Vu', 'dung.vu3@example.com', 'General', '2026-07-09 13:00:00', 300000.00, 'completed', 'Tai kham'),
  ('APT-2026-0008', 'Hieu Phan', 'hieu.phan21@example.com', 'ENT', '2026-08-17 16:00:00', 450000.00, 'pending', 'Kham lan dau'),
  ('APT-2026-0009', 'Dat Phan', 'dat.phan20@example.com', 'Pediatrics', '2026-08-02 08:00:00', 500000.00, 'confirmed', ''),
  ('APT-2026-0010', 'Khanh Duong', 'khanh.duong8@example.com', 'Cardiology', '2026-09-18 15:00:00', 1500000.00, 'pending', 'Kham lan dau'),
  ('APT-2026-0011', 'Son Hoang', 'son.hoang2@example.com', 'ENT', '2026-07-18 08:00:00', 500000.00, 'confirmed', 'Thanh toan tien mat'),
  ('APT-2026-0012', 'Thao Phan', 'thao.phan4@example.com', 'General', '2026-08-12 08:00:00', 500000.00, 'confirmed', ''),
  ('APT-2026-0013', 'Nam Hoang', 'nam.hoang7@example.com', 'ENT', '2026-06-12 15:00:00', 250000.00, 'confirmed', 'Tai kham'),
  ('APT-2026-0014', 'Lan Bui', 'lan.bui5@example.com', 'Pediatrics', '2026-06-06 14:00:00', 750000.00, 'confirmed', 'Co bao hiem'),
  ('APT-2026-0015', 'Lan Bui', 'lan.bui5@example.com', 'Ophthalmology', '2026-06-13 08:00:00', 1000000.00, 'confirmed', 'Tai kham'),
  ('APT-2026-0016', 'Ha Hoang', 'ha.hoang14@example.com', 'Dermatology', '2026-08-27 11:00:00', 300000.00, 'pending', ''),
  ('APT-2026-0017', 'Vy Pham', 'vy.pham6@example.com', 'Pediatrics', '2026-08-09 09:00:00', 450000.00, 'completed', ''),
  ('APT-2026-0018', 'Khanh Nguyen', 'khanh.nguyen16@example.com', 'Pediatrics', '2026-08-01 09:00:00', 450000.00, 'confirmed', 'Uu tien buoi sang'),
  ('APT-2026-0019', 'Khanh Duong', 'khanh.duong8@example.com', 'General', '2026-06-20 15:00:00', 500000.00, 'completed', 'Thanh toan tien mat'),
  ('APT-2026-0020', 'Khanh Pham', 'khanh.pham19@example.com', 'Dentistry', '2026-06-13 11:00:00', 450000.00, 'pending', ''),
  ('APT-2026-0021', 'Bao Pham', 'bao.pham13@example.com', 'General', '2026-07-12 15:00:00', 200000.00, 'completed', 'Uu tien buoi sang'),
  ('APT-2026-0022', 'Lan Do', 'lan.do10@example.com', 'ENT', '2026-06-24 13:00:00', 1500000.00, 'completed', ''),
  ('APT-2026-0023', 'Bao Pham', 'bao.pham13@example.com', 'Dermatology', '2026-09-23 13:00:00', 1500000.00, 'confirmed', 'Tai kham'),
  ('APT-2026-0024', 'Bao Pham', 'bao.pham13@example.com', 'ENT', '2026-09-22 10:00:00', 450000.00, 'cancelled', 'Uu tien buoi sang');
