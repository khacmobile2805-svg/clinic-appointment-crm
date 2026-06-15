<?php

/**
 * Cấu hình ứng dụng.
 *
 * 'debug' là công tắc môi trường (Week 6-4 - Production Safe Error): để TRUE khi
 * phát triển, đổi sang FALSE khi chạy thật để trang lỗi không bao giờ lộ
 * SQLSTATE, tên bảng, đường dẫn hay stack trace cho người dùng.
 */
return [
    'name'            => 'Clinic Appointment CRM',
    'debug'           => true,              // PRODUCTION: đặt false
    'timezone'        => 'Asia/Ho_Chi_Minh',
    'session_timeout' => 1800,             // số giây không hoạt động trước khi buộc đăng nhập lại (Idle Timeout)
    'rate_limit_secs' => 5,                // khoảng cách tối thiểu giữa 2 lần gửi form công khai hợp lệ
];
