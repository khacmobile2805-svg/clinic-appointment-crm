<?php

/**
 * Các hàm tiện ích dùng chung, nạp một lần trong public/index.php sau autoloader.
 * Cố tình giữ nhỏ gọn; logic nghiệp vụ nằm ở tầng Service, không nằm ở đây.
 */

/**
 * Đọc một giá trị từ cấu hình ứng dụng (config/app.php), lưu trong $GLOBALS lúc khởi động.
 */
function config(string $key, mixed $default = null): mixed
{
    return $GLOBALS['app_config'][$key] ?? $default;
}

/**
 * e() — Lớp phòng thủ XSS (Week 4-1, bước "Neutralize").
 * Mã hóa mọi giá trị trước khi in ra HTML. Bắt buộc dùng trong View cho mọi
 * dữ liệu đến từ CSDL hoặc người dùng, vì TUYỆT ĐỐI không echo trực tiếp (Direct Echoing).
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * redirect() — Gửi header Location rồi DỪNG NGAY bằng exit.
 * Là lõi của mẫu PRG (Week 4-3): "Always lock the door with exit;".
 */
function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

/**
 * render() — Dựng View bên trong Layout bằng Output Buffering (Week 8-3).
 * ob_start() hứng toàn bộ HTML của View con vào $content, rồi nhúng vào Layout tổng,
 * nhờ vậy View con không phải lặp lại <html>/<head>.
 */
function render(string $view, array $data = [], string $layout = 'layouts/main'): void
{
    extract($data, EXTR_SKIP);

    ob_start();
    require __DIR__ . '/../Views/' . $view . '.php';
    $content = ob_get_clean();

    // old input chỉ dùng một lần: xóa ngay sau khi một View đã render xong.
    unset($_SESSION['old']);

    require __DIR__ . '/../Views/' . $layout . '.php';
}

/**
 * partial() — Nhúng một mảnh giao diện (nav, flash...) không kèm Layout.
 */
function partial(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require __DIR__ . '/../Views/partials/' . $name . '.php';
}

/**
 * flash() — Lưu một thông báo "dùng một lần" (Flash Message - Week 5-2).
 */
function flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

/**
 * get_flash() — Đọc và XÓA NGAY thông báo flash, đảm bảo nó chỉ hiển thị đúng một lần.
 */
function get_flash(string $key): ?string
{
    if (empty($_SESSION['flash'][$key])) {
        return null;
    }
    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $message;
}

/**
 * old() — Giữ lại dữ liệu đã nhập để refill form khi validate lỗi (Sticky Form - Week 4-2,
 * "Empathetic UX"). Controller gán $_SESSION['old'] = $_POST trước khi render lại;
 * render() sẽ xóa nó đi nên không bao giờ rò rỉ sang request sau.
 */
function old(string $key, string $default = ''): string
{
    return $_SESSION['old'][$key] ?? $default;
}

/**
 * status_label() — Đổi mã trạng thái (DB) sang nhãn tiếng Việt để hiển thị.
 * Gom một chỗ để nhãn nhất quán giữa danh sách và form (View không tự chế nhãn).
 */
function status_label(string $status): string
{
    return [
        'new'       => 'Mới',
        'contacted' => 'Đã liên hệ',
        'scheduled' => 'Đã đặt lịch',
        'inactive'  => 'Ngừng',
        'pending'   => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ][$status] ?? $status;
}

/**
 * department_label() — Đổi tên khoa (tiếng Anh) sang tiếng Việt.
 */
function department_label(string $department): string
{
    return [
        'General'       => 'Đa khoa',
        'Cardiology'    => 'Tim mạch',
        'Pediatrics'    => 'Nhi khoa',
        'Dermatology'   => 'Da liễu',
        'Neurology'     => 'Thần kinh',
        'Orthopedics'   => 'Chấn thương chỉnh hình',
        'ENT'           => 'Tai Mũi Họng',
        'Ophthalmology' => 'Nhãn khoa',
        'Dentistry'     => 'Răng Hàm Mặt',
    ][$department] ?? $department;
}

/**
 * CSRF (Week 4 - form security): token đồng bộ chống Cross-Site Request Forgery.
 * Cookie SameSite=Lax đã chặn phần lớn; token này là lớp phòng thủ thứ hai cho MỌI form POST.
 */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/** Ô hidden chứa token để nhúng vào mỗi <form> POST. */
function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

/** So khớp token gửi lên với token trong session (hash_equals chống timing attack). */
function csrf_verify(?string $token): bool
{
    return !empty($_SESSION['_csrf']) && is_string($token) && hash_equals($_SESSION['_csrf'], $token);
}

/**
 * require_login() — Chốt bảo vệ cho các route cần đăng nhập:
 *   - chưa đăng nhập  -> đẩy về /login
 *   - quá hạn không hoạt động -> phá hủy phiên (Idle Timeout - Week 5-2) rồi bắt đăng nhập lại
 * Gọi ở đầu mỗi action yêu cầu người dùng đã đăng nhập.
 */
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        flash('error', 'Vui lòng đăng nhập để tiếp tục.');
        redirect('/login');
    }

    $timeout = (int) config('session_timeout', 1800);
    $last    = (int) ($_SESSION['last_activity'] ?? time());

    if ($timeout > 0 && (time() - $last) > $timeout) {
        // Timeout không phải chỉ "unset một biến": phải phá hủy phiên hoàn toàn.
        $_SESSION = [];
        session_regenerate_id(true);
        flash('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
        redirect('/login');
    }

    $_SESSION['last_activity'] = time();
}
