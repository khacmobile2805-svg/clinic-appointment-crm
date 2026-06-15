<?php

/**
 * HealthController — GET /health : kiểm tra sức khỏe app + CSDL, trả JSON.
 *
 * CSDL chỉ báo "up"/"down": lỗi kết nối bị bắt và KHÔNG lộ ra ngoài, nên
 * endpoint này không rò rỉ thông tin đăng nhập hay SQLSTATE (Week 6-4).
 */
class HealthController
{
    public function __construct(private array $dbConfig)
    {
    }

    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $database = 'down';
        try {
            $pdo = Database::connect($this->dbConfig);
            $pdo->query('SELECT 1');
            $database = 'up';
        } catch (Throwable $e) {
            $database = 'down';
        }

        echo json_encode(
            [
                'app'      => 'up',
                'name'     => config('name'),
                'database' => $database,
                'time'     => date('c'),
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }
}
