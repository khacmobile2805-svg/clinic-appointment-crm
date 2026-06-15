<?php

/**
 * AuthController — Controller mỏng cho luồng đăng nhập/đăng xuất (Week 8-2).
 * Chỉ đọc request, gọi AuthService, rồi render hoặc redirect. Không SQL, không logic nghiệp vụ.
 */
class AuthController
{
    public function __construct(private AuthService $auth)
    {
    }

    /** GET /login */
    public function login(): void
    {
        if (!empty($_SESSION['user_id'])) {
            redirect('/dashboard');
        }
        render('auth/login', ['title' => 'Đăng nhập', 'errors' => []]);
    }

    /** POST /login */
    public function handleLogin(): void
    {
        $result = $this->auth->attempt($_POST['email'] ?? '', $_POST['password'] ?? '');

        if (!$result['success']) {
            // Validate lỗi -> ở lại trang POST, hiện lỗi, giữ lại email đã nhập (Sticky Form).
            $_SESSION['old'] = ['email' => trim((string) ($_POST['email'] ?? ''))];
            render('auth/login', ['title' => 'Đăng nhập', 'errors' => $result['errors']]);
            return;
        }

        // Chống Session Fixation (Week 5-4): cấp session ID HOÀN TOÀN MỚI ngay khi danh
        // tính được xác thực, khiến ID cũ mà kẻ tấn công có thể đã "cấy" trước trở nên vô dụng.
        session_regenerate_id(true);

        $_SESSION['user_id']       = $result['user']['id'];
        $_SESSION['user_name']     = $result['user']['name'];
        $_SESSION['user_role']     = $result['user']['role'];
        $_SESSION['last_activity'] = time();

        // PRG: thành công thì 302 redirect (Week 4-3), kèm Flash dùng một lần.
        flash('success', 'Đăng nhập thành công. Xin chào ' . $result['user']['name'] . '!');
        redirect('/dashboard');
    }

    /** POST /logout — Logout sạch (Week 5-3): phá hủy phiên hoàn toàn, không chỉ unset biến. */
    public function logout(): void
    {
        // 1) Xóa toàn bộ dữ liệu phiên.
        session_unset();

        // 2) Xóa cookie phiên ở trình duyệt bằng thời gian hết hạn trong quá khứ (âm).
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly']
            );
        }

        // 3) Phá hủy phiên phía server.
        session_destroy();

        // 4) Mở phiên mới sạch chỉ để mang Flash "đã đăng xuất" sang trang /login.
        session_start();
        flash('success', 'Bạn đã đăng xuất an toàn.');
        redirect('/login');
    }
}
