<?php

/**
 * PatientRepository — Toàn bộ truy vấn cho bảng patients (Week 8-1, Week 6-3).
 * Chỉ prepared statement; không đọc $_POST, không in HTML.
 *
 * LƯU Ý sắp xếp: $sortColumn / $sortDir được nội suy thẳng vào câu SQL nên BẮT BUỘC
 * đã được kiểm tra bằng Whitelist ở PatientService trước khi tới đây (Week 6-3:
 * PDO không bind được tên cột / từ khóa ORDER BY).
 */
class PatientRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function countAll(string $keyword = ''): int
    {
        $sql = "SELECT COUNT(*) AS total FROM patients";
        $params = [];

        if ($keyword !== '') {
            // EMULATE_PREPARES=false: một named placeholder không được tái dùng nhiều lần,
            // nên tách thành :kw1/:kw2/:kw3 cùng trỏ về một giá trị.
            $sql .= " WHERE full_name LIKE :kw1 OR email LIKE :kw2 OR phone LIKE :kw3";
            $like = '%' . $keyword . '%';
            $params = ['kw1' => $like, 'kw2' => $like, 'kw3' => $like];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) ($stmt->fetch()['total'] ?? 0);
    }

    public function getPaginated(string $keyword, string $sortColumn, string $sortDir, int $limit, int $offset): array
    {
        // Chỉ lấy đúng cột cần cho danh sách, không SELECT * (Week 7-4).
        $sql = "SELECT id, full_name, email, phone, gender, status, created_at
                  FROM patients";
        $params = [];

        if ($keyword !== '') {
            $sql .= " WHERE full_name LIKE :kw1 OR email LIKE :kw2 OR phone LIKE :kw3";
            $like = '%' . $keyword . '%';
            $params = ['kw1' => $like, 'kw2' => $like, 'kw3' => $like];
        }

        // $sortColumn/$sortDir đã được whitelist ở Service nên an toàn khi nội suy.
        $sql .= " ORDER BY {$sortColumn} {$sortDir} LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        // LIMIT/OFFSET ép PARAM_INT (Week 7-2).
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Kiểm tra email đã tồn tại chưa, BỎ QUA bản ghi đang sửa (id <> :id) — Week 7-3.
     * Khi tạo mới thì truyền $excludeId = 0.
     */
    public function emailExists(string $email, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM patients WHERE email = :email AND id <> :id LIMIT 1"
        );
        $stmt->execute(['email' => $email, 'id' => $excludeId]);
        return (bool) $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO patients (full_name, email, phone, gender, status, note)
                VALUES (:full_name, :email, :phone, :gender, :status, :note)";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute($data);
        } catch (PDOException $e) {
            // 1062 = vi phạm UNIQUE: chốt vật lý cuối cùng chống Race Condition (Week 7-3).
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                throw new DuplicateRecordException('Trùng email bệnh nhân.');
            }
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $sql = "UPDATE patients
                   SET full_name = :full_name, email = :email, phone = :phone,
                       gender = :gender, status = :status, note = :note,
                       updated_at = NOW()
                 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        try {
            return $stmt->execute($data);
        } catch (PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                throw new DuplicateRecordException('Trùng email bệnh nhân.');
            }
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM patients WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
