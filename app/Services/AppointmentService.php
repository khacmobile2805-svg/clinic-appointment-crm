<?php

/**
 * AppointmentService — Logic nghiệp vụ module Lịch hẹn (Week 8-1/8-2).
 * Khác Bệnh nhân ở quy tắc:
 *   - khóa nghiệp vụ là appointment_code (unique)
 *   - fee phải là số và >= 0
 *   - scheduled_at bắt buộc và phải là mốc thời gian hợp lệ
 *   - status thuộc whitelist: pending | confirmed | completed | cancelled
 */
class AppointmentService
{
    private const SORTS = ['id', 'appointment_code', 'patient_name', 'status', 'scheduled_at', 'fee', 'created_at'];
    private const STATUSES = ['pending', 'confirmed', 'completed', 'cancelled'];
    private const DEPARTMENTS = ['General', 'Cardiology', 'Dermatology', 'Pediatrics', 'Dentistry', 'ENT', 'Ophthalmology'];
    private const PER_PAGE = 10;

    public function __construct(private AppointmentRepository $repo)
    {
    }

    public function getAppointmentList(array $q): array
    {
        $keyword = trim((string) ($q['q'] ?? ''));

        // Whitelist sort/direction (Week 6-3): giá trị lạ rơi về created_at.
        $sort = in_array($q['sort'] ?? '', self::SORTS, true) ? $q['sort'] : 'created_at';
        $dir  = strtolower((string) ($q['direction'] ?? '')) === 'asc' ? 'ASC' : 'DESC';

        $totalItems = $this->repo->countAll($keyword);
        $totalPages = max(1, (int) ceil($totalItems / self::PER_PAGE));

        // Chuẩn hóa trang: âm -> 1 ; vượt quá -> trang cuối.
        $page   = max(1, (int) ($q['page'] ?? 1));
        $page   = min($page, $totalPages);
        $offset = ($page - 1) * self::PER_PAGE;

        return [
            'appointments' => $this->repo->getPaginated($keyword, $sort, $dir, self::PER_PAGE, $offset),
            'keyword'      => $keyword,
            'sort'         => $sort,
            'direction'    => strtolower($dir),
            'page'         => $page,
            'totalPages'   => $totalPages,
            'totalItems'   => $totalItems,
            'perPage'      => self::PER_PAGE,
            'departments'  => self::DEPARTMENTS,
        ];
    }

    public function departments(): array
    {
        return self::DEPARTMENTS;
    }

    public function getAppointmentForEdit(int $id): ?array
    {
        return $this->repo->findById($id);
    }

    public function createAppointment(array $input): array
    {
        $v = $this->validate($input);
        if ($v['errors']) { // Success Gate (Week 4-2)
            return ['success' => false, 'errors' => $v['errors']];
        }

        if ($this->repo->codeExists($v['values']['appointment_code'], 0)) {
            return ['success' => false, 'errors' => ['appointment_code' => 'Mã lịch hẹn này đã tồn tại.']];
        }

        try {
            $this->repo->create($v['values']);
            return ['success' => true, 'errors' => []];
        } catch (DuplicateRecordException $e) {
            // Chốt vật lý chống Race Condition (Week 7-3).
            return ['success' => false, 'errors' => ['appointment_code' => 'Mã lịch hẹn này đã tồn tại.']];
        }
    }

    public function updateAppointment(int $id, array $input): array
    {
        if (!$this->repo->findById($id)) {
            return ['success' => false, 'errors' => ['general' => 'Lịch hẹn không tồn tại.']];
        }

        $v = $this->validate($input);
        if ($v['errors']) {
            return ['success' => false, 'errors' => $v['errors']];
        }

        // Trùng mã nhưng bỏ qua chính bản ghi đang sửa (id <> :ignore_id) - Week 7-3.
        if ($this->repo->codeExists($v['values']['appointment_code'], $id)) {
            return ['success' => false, 'errors' => ['appointment_code' => 'Mã lịch hẹn này đã tồn tại.']];
        }

        try {
            $this->repo->update($id, $v['values']);
            return ['success' => true, 'errors' => []];
        } catch (DuplicateRecordException $e) {
            return ['success' => false, 'errors' => ['appointment_code' => 'Mã lịch hẹn này đã tồn tại.']];
        }
    }

    public function deleteAppointment(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'errors' => ['general' => 'ID không hợp lệ.']];
        }
        $this->repo->delete($id);
        return ['success' => true, 'errors' => []];
    }

    /**
     * Validate theo trình tự Rỗng -> Định dạng -> Độ dài/Nghiệp vụ (Week 4-2).
     * @return array{errors:array<string,string>, values:array<string,mixed>}
     */
    private function validate(array $input): array
    {
        $errors = [];

        $appointment_code = trim((string) ($input['appointment_code'] ?? ''));
        $patient_name     = trim((string) ($input['patient_name'] ?? ''));
        $patient_email    = trim((string) ($input['patient_email'] ?? ''));
        $department       = trim((string) ($input['department'] ?? 'General'));
        $scheduled_raw    = trim((string) ($input['scheduled_at'] ?? ''));
        $fee_raw          = trim((string) ($input['fee'] ?? ''));
        $status           = trim((string) ($input['status'] ?? 'pending'));
        $note             = trim((string) ($input['note'] ?? ''));

        if ($appointment_code === '') {
            $errors['appointment_code'] = 'Mã lịch hẹn không được để trống.';
        } elseif (mb_strlen($appointment_code) > 50) {
            $errors['appointment_code'] = 'Mã lịch hẹn tối đa 50 ký tự.';
        }

        if ($patient_name === '') {
            $errors['patient_name'] = 'Tên bệnh nhân không được để trống.';
        } elseif (mb_strlen($patient_name) > 100) {
            $errors['patient_name'] = 'Tên bệnh nhân tối đa 100 ký tự.';
        }

        if ($patient_email !== '') {
            if (!filter_var($patient_email, FILTER_VALIDATE_EMAIL)) {
                $errors['patient_email'] = 'Email không đúng định dạng.';
            } elseif (mb_strlen($patient_email) > 150) {
                $errors['patient_email'] = 'Email tối đa 150 ký tự.';
            }
        }

        // Khoa: whitelist, giá trị lạ -> General.
        if (!in_array($department, self::DEPARTMENTS, true)) {
            $department = 'General';
        }

        // scheduled_at: bắt buộc + phải parse được thành mốc thời gian thật.
        $scheduled_at = '';
        if ($scheduled_raw === '') {
            $errors['scheduled_at'] = 'Thời gian khám không được để trống.';
        } else {
            $ts = strtotime($scheduled_raw);
            if ($ts === false) {
                $errors['scheduled_at'] = 'Thời gian khám không hợp lệ.';
            } else {
                $scheduled_at = date('Y-m-d H:i:s', $ts);
            }
        }

        // fee: là số và >= 0 (nghiệp vụ).
        $fee = 0.0;
        if ($fee_raw === '' || !is_numeric($fee_raw)) {
            $errors['fee'] = 'Phí khám phải là số.';
        } elseif ((float) $fee_raw < 0) {
            $errors['fee'] = 'Phí khám không được âm.';
        } else {
            $fee = (float) $fee_raw;
        }

        if (!in_array($status, self::STATUSES, true)) {
            $errors['status'] = 'Trạng thái không hợp lệ.';
        }

        return [
            'errors' => $errors,
            'values' => compact(
                'appointment_code',
                'patient_name',
                'patient_email',
                'department',
                'scheduled_at',
                'fee',
                'status',
                'note'
            ),
        ];
    }
}
