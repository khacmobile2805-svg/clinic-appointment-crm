<?php

/**
 * AppointmentRepository — Toàn bộ truy vấn cho bảng appointments.
 * Chỉ prepared statement; không đọc $_POST, không in HTML. Khóa nghiệp vụ: appointment_code.
 *
 * $sortColumn / $sortDir BẮT BUỘC đã được whitelist ở AppointmentService (Week 6-3).
 */
class AppointmentRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function countAll(string $keyword = ''): int
    {
        $sql = "SELECT COUNT(*) AS total FROM appointments";
        $params = [];

        if ($keyword !== '') {
            $sql .= " WHERE appointment_code LIKE :kw1 OR patient_name LIKE :kw2 OR patient_email LIKE :kw3";
            $like = '%' . $keyword . '%';
            $params = ['kw1' => $like, 'kw2' => $like, 'kw3' => $like];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function getPaginated(string $keyword, string $sortColumn, string $sortDir, int $limit, int $offset): array
    {
        $sql = "SELECT id, appointment_code, patient_name, patient_email, department, scheduled_at, fee, status
                  FROM appointments";
        $params = [];

        if ($keyword !== '') {
            $sql .= " WHERE appointment_code LIKE :kw1 OR patient_name LIKE :kw2 OR patient_email LIKE :kw3";
            $like = '%' . $keyword . '%';
            $params = ['kw1' => $like, 'kw2' => $like, 'kw3' => $like];
        }

        $sql .= " ORDER BY {$sortColumn} {$sortDir} LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM appointments WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Kiểm tra mã lịch hẹn đã tồn tại chưa, bỏ qua bản ghi đang sửa (id <> :id) — Week 7-3. */
    public function codeExists(string $code, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM appointments WHERE appointment_code = :code AND id <> :id LIMIT 1"
        );
        $stmt->execute(['code' => $code, 'id' => $excludeId]);
        return (bool) $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO appointments
                    (appointment_code, patient_name, patient_email, department, scheduled_at, fee, status, note)
                VALUES
                    (:appointment_code, :patient_name, :patient_email, :department, :scheduled_at, :fee, :status, :note)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute($data);
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                throw new DuplicateRecordException('Trùng mã lịch hẹn.');
            }
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $sql = "UPDATE appointments
                   SET appointment_code = :appointment_code, patient_name = :patient_name,
                       patient_email = :patient_email, department = :department,
                       scheduled_at = :scheduled_at, fee = :fee, status = :status,
                       note = :note, updated_at = NOW()
                 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute($data);
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                throw new DuplicateRecordException('Trùng mã lịch hẹn.');
            }
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM appointments WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
