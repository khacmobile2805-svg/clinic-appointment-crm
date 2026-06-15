<?php

/**
 * RegistrationController — Form đăng ký khám CÔNG KHAI (không đăng nhập).
 * Minh họa chống spam cơ bản trước khi lưu (Week 4-4):
 *   1. Honeypot   : field ẩn "website" mà bot hay điền (Bẫy Không Gian).
 *   2. Rate limit : khoảng cách tối thiểu giữa 2 lần gửi hợp lệ (Bẫy Thời Gian).
 *   3. PRG        : redirect sau POST thành công để F5 không gửi trùng.
 * Đăng ký công khai tạo một bệnh nhân mới với trạng thái "new".
 */
class RegistrationController
{
    public function __construct(private PatientService $service)
    {
    }

    /** GET /register */
    public function show(): void
    {
        render('registration/form', ['title' => 'Đăng ký khám', 'errors' => []]);
    }

    /** POST /register */
    public function submit(): void
    {
        // 1) Honeypot — Silent Reject (Week 4-4): "Never Tell a Bot It's a Bot".
        //    Người thật không bao giờ thấy/điền field này. Nếu có dữ liệu -> coi như bot,
        //    KHÔNG lưu, phản hồi mơ hồ để bot không phát hiện ra bẫy.
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            flash('success', 'Cảm ơn bạn đã đăng ký. Phòng khám sẽ liên hệ sớm.');
            redirect('/register');
        }

        // 2) Rate limit theo phiên: chặn nếu gửi lại quá nhanh kể từ lần HỢP LỆ gần nhất.
        $now  = time();
        $last = (int) ($_SESSION['last_public_submit'] ?? 0);
        $wait = (int) config('rate_limit_secs', 5);
        if ($now - $last < $wait) {
            $_SESSION['old'] = $_POST;
            render('registration/form', [
                'title'  => 'Đăng ký khám',
                'errors' => ['general' => 'Bạn thao tác quá nhanh, vui lòng thử lại sau vài giây.'],
            ]);
            return;
        }

        // 3) Validate + tạo (công khai => status ép cứng "new").
        $result = $this->service->createPatient($_POST, isPublic: true);
        if (!$result['success']) {
            $_SESSION['old'] = $_POST;
            render('registration/form', ['title' => 'Đăng ký khám', 'errors' => $result['errors']]);
            return;
        }

        // CRUCIAL DETAIL (Week 4-4): CHỈ cập nhật đồng hồ rate limit khi form hợp lệ 100%
        // (đã lưu thành công), không phải mỗi lần submit.
        $_SESSION['last_public_submit'] = $now;

        // 4) PRG: thành công thì redirect.
        flash('success', 'Đăng ký thành công! Phòng khám sẽ liên hệ với bạn sớm.');
        redirect('/register');
    }
}
