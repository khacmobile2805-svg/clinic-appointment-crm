<?php

/**
 * PatientService — Toàn bộ logic nghiệp vụ của module Bệnh nhân (Week 8-1/8-2).
 *  - validate phía server theo trình tự Rỗng -> Định dạng -> Độ dài (Week 4-2)
 *  - phân trang + Whitelist sắp xếp an toàn (Week 6-3, 7-2)
 *  - xử lý trùng email: kiểm tra tầng ứng dụng (id <> :ignore_id) + bắt 1062 làm chốt vật lý
 *  - nhánh tạo công khai (status ép cứng là 'new')
 * Controller gọi service này; service không chạm SQL, không in HTML.
 */
class PatientService
{
    /** Các cột được phép sắp xếp. Giá trị khác -> rơi về mặc định created_at (Whitelist - Week 6-3). */
    private const SORTS = ['id', 'full_name', 'email', 'status', 'created_at'];
    private const STATUSES = ['new', 'contacted', 'scheduled', 'inactive'];
    private const GENDERS  = ['male', 'female', 'other'];
    private const PER_PAGE = 10;

    public function __construct(private PatientRepository $repo)
    {
    }

    public function getPatientList(array $q): array
    {
        $keyword = trim((string) ($q['q'] ?? ''));

        // Whitelist sort + direction: "sort=id;DROP TABLE" không nằm trong danh sách
        // nên bị bỏ qua, dùng mặc định -> không bao giờ tới được SQL (Week 6-3).
        $sort = in_array($q['sort'] ?? '', self::SORTS, true) ? $q['sort'] : 'created_at';
        $dir  = strtolower((string) ($q['direction'] ?? '')) === 'asc' ? 'ASC' : 'DESC';

        $totalItems = $this->repo->countAll($keyword);
        $totalPages = max(1, (int) ceil($totalItems / self::PER_PAGE));

        // Chuẩn hóa trang: âm/bằng 0 -> 1 ; vượt quá -> trang cuối.
        $page   = max(1, (int) ($q['page'] ?? 1));
        $page   = min($page, $totalPages);
        $offset = ($page - 1) * self::PER_PAGE;

        return [
            'patients'   => $this->repo->getPaginated($keyword, $sort, $dir, self::PER_PAGE, $offset),
            'keyword'    => $keyword,
            'sort'       => $sort,
            'direction'  => strtolower($dir),
            'page'       => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'perPage'    => self::PER_PAGE,
        ];
    }

    public function getPatientForEdit(int $id): ?array
    {
        return $this->repo->findById($id);
    }

    public function createPatient(array $input, bool $isPublic = false): array
    {
        $v = $this->validate($input, allowStatus: !$isPublic);
        // Success Gate (Week 4-2): chỉ chạm DB khi mảng lỗi rỗng.
        if ($v['errors']) {
            return ['success' => false, 'errors' => $v['errors']];
        }

        // Kiểm tra trùng ở tầng ứng dụng (excludeId = 0 vì tạo mới).
        if ($this->repo->emailExists($v['values']['email'], 0)) {
            return ['success' => false, 'errors' => ['email' => 'Email này đã tồn tại trong hệ thống.']];
        }

        try {
            $this->repo->create($v['values']);
            return ['success' => true, 'errors' => []];
        } catch (DuplicateRecordException $e) {
            // Chốt vật lý chống Race Condition (Week 7-3): 2 request cùng lúc đều qua được
            // bước check trên, nhưng UNIQUE ở DB chặn lại và ném 1062.
            return ['success' => false, 'errors' => ['email' => 'Email này đã tồn tại trong hệ thống.']];
        }
    }

    public function updatePatient(int $id, array $input): array
    {
        if (!$this->repo->findById($id)) {
            return ['success' => false, 'errors' => ['general' => 'Bệnh nhân không tồn tại.']];
        }

        $v = $this->validate($input, allowStatus: true);
        if ($v['errors']) {
            return ['success' => false, 'errors' => $v['errors']];
        }

        // Trùng email nhưng BỎ QUA chính bản ghi đang sửa (id <> :ignore_id) - Week 7-3.
        if ($this->repo->emailExists($v['values']['email'], $id)) {
            return ['success' => false, 'errors' => ['email' => 'Email này đã tồn tại trong hệ thống.']];
        }

        try {
            $this->repo->update($id, $v['values']);
            return ['success' => true, 'errors' => []];
        } catch (DuplicateRecordException $e) {
            return ['success' => false, 'errors' => ['email' => 'Email này đã tồn tại trong hệ thống.']];
        }
    }

    public function deletePatient(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'errors' => ['general' => 'ID không hợp lệ.']];
        }
        $this->repo->delete($id);
        return ['success' => true, 'errors' => []];
    }

    /**
     * Validate theo trình tự Rỗng -> Định dạng -> Độ dài (Week 4-2).
     * @return array{errors:array<string,string>, values:array<string,string>}
     */
    private function validate(array $input, bool $allowStatus): array
    {
        $errors = [];

        // Lớp 1+2: Fallback (?? '') và Normalize (trim) - Week 4-1.
        $full_name = trim((string) ($input['full_name'] ?? ''));
        $email     = trim((string) ($input['email'] ?? ''));
        $phone     = trim((string) ($input['phone'] ?? ''));
        $gender    = trim((string) ($input['gender'] ?? 'other'));
        $status    = trim((string) ($input['status'] ?? 'new'));
        $note      = trim((string) ($input['note'] ?? ''));

        // Rỗng
        if ($full_name === '') {
            $errors['full_name'] = 'Họ tên không được để trống.';
        } elseif (mb_strlen($full_name) > 100) { // Độ dài (khớp VARCHAR(100))
            $errors['full_name'] = 'Họ tên tối đa 100 ký tự.';
        }

        // Rỗng -> Định dạng -> Độ dài
        if ($email === '') {
            $errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng.';
        } elseif (mb_strlen($email) > 150) {
            $errors['email'] = 'Email tối đa 150 ký tự.';
        }

        if ($phone !== '' && mb_strlen($phone) > 30) {
            $errors['phone'] = 'Số điện thoại tối đa 30 ký tự.';
        }

        // Whitelist nghiệp vụ
        if (!in_array($gender, self::GENDERS, true)) {
            $gender = 'other';
        }
        if ($allowStatus) {
            if (!in_array($status, self::STATUSES, true)) {
                $errors['status'] = 'Trạng thái không hợp lệ.';
            }
        } else {
            $status = 'new'; // đăng ký công khai luôn bắt đầu ở trạng thái "new"
        }

        return [
            'errors' => $errors,
            'values' => compact('full_name', 'email', 'phone', 'gender', 'status', 'note'),
        ];
    }
}
