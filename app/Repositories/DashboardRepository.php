<?php

/**
 * DashboardRepository — Các truy vấn tổng hợp (chỉ đọc) cho màn hình tổng quan.
 * Không SELECT * ở danh sách (Week 7-4): chỉ lấy đúng cột cần dùng.
 */
class DashboardRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function patientCountByStatus(string $status): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS c FROM patients WHERE status = :s");
        $stmt->execute(['s' => $status]);
        return (int) ($stmt->fetch()['c'] ?? 0);
    }

    public function totalAppointments(): int
    {
        $row = $this->db->query("SELECT COUNT(*) AS c FROM appointments")->fetch();
        return (int) ($row['c'] ?? 0);
    }

    /** Doanh thu = tổng phí của các lịch hẹn đã xác nhận hoặc hoàn thành. */
    public function revenue(): float
    {
        $sql = "SELECT COALESCE(SUM(fee), 0) AS s
                  FROM appointments
                 WHERE status IN ('confirmed', 'completed')";
        $row = $this->db->query($sql)->fetch();
        return (float) ($row['s'] ?? 0);
    }

    public function recentPatients(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT full_name, email, status, created_at
               FROM patients
              ORDER BY created_at DESC
              LIMIT :limit"
        );
        // LIMIT bắt buộc ép kiểu PARAM_INT (Week 7-2) để tránh lỗi cú pháp MySQL.
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
