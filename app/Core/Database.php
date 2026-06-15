<?php

/**
 * Database — Tạo kết nối PDO dùng chung cho mọi Repository (Week 6-2).
 *
 * Bốn tham số bắt buộc dưới đây quyết định độ an toàn của tầng dữ liệu:
 *  - charset=utf8mb4          : lưu đúng Unicode (tiếng Việt, emoji), tránh lỗi mã hóa.
 *  - ERRMODE_EXCEPTION        : lỗi SQL ném exception để xử lý tập trung, không nuốt lỗi âm thầm.
 *  - FETCH_ASSOC             : trả về mảng kết hợp; View chỉ đọc theo tên cột.
 *  - EMULATE_PREPARES = false : giao việc chuẩn bị câu lệnh cho chính MySQL (prepared
 *                               statement thật) — lớp phòng thủ chính chống SQL Injection.
 */
class Database
{
    public static function connect(array $config): PDO
    {
        // DSN gom host + tên CSDL + charset; mọi tham số lấy từ config, không hardcode.
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['database'],
            $config['charset']
        );

        return new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
}
