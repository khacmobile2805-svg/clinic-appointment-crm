<?php

declare(strict_types=1);

/**
 * Front Controller — Cổng vào DUY NHẤT cho mọi request (Week 8-1, "Quy Tắc Vàng").
 *
 *   Chạy:  php -S localhost:8000 -t public
 *
 * Người dùng chỉ chạm tới public/index.php; toàn bộ app/Core, app/Repositories...
 * nằm ngoài web root nên không thể truy cập trực tiếp.
 */

// ---------------------------------------------------------------------------
// 1. Cấu hình
// ---------------------------------------------------------------------------
$appConfig = require __DIR__ . '/../config/app.php';
$dbConfig  = require __DIR__ . '/../config/database.php';
$GLOBALS['app_config'] = $appConfig;

date_default_timezone_set($appConfig['timezone'] ?? 'UTC');

// ---------------------------------------------------------------------------
// 2. Báo lỗi (Week 6-4): chỉ HIỂN THỊ khi debug; luôn GHI LOG.
// ---------------------------------------------------------------------------
error_reporting(E_ALL);
ini_set('display_errors', $appConfig['debug'] ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../storage/logs/app.log');

// ---------------------------------------------------------------------------
// 3. Autoloader (không dùng Composer) + helpers
//    Class được tìm theo tên trong các thư mục con của app/.
// ---------------------------------------------------------------------------
spl_autoload_register(function (string $class): void {
    foreach (['Core', 'Controllers', 'Services', 'Repositories'] as $dir) {
        $file = __DIR__ . "/../app/{$dir}/{$class}.php";
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

require __DIR__ . '/../app/Core/helpers.php';

// ---------------------------------------------------------------------------
// 4. Session (Week 5-1): cấu hình cookie BẮT BUỘC trước session_start(),
//    và không được echo HTML trước đó.
//      - httponly : JavaScript không đọc được cookie phiên (giảm rủi ro XSS đánh cắp)
//      - secure   : chỉ gửi cookie qua HTTPS (chống sniff mạng)
//      - samesite : Lax -> giảm rủi ro CSRF từ request cross-site
// ---------------------------------------------------------------------------
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// ---------------------------------------------------------------------------
// 5. Xử lý lỗi an toàn cho Production (Week 6-4, "Graceful Failure").
//    Người dùng chỉ thấy trang 500 chung chung; nguyên nhân thật ghi vào storage/logs/app.log.
// ---------------------------------------------------------------------------
set_exception_handler(function (Throwable $e) use ($appConfig): void {
    error_log('[' . date('c') . '] ' . $e->getMessage()
        . ' @ ' . $e->getFile() . ':' . $e->getLine());

    if (!headers_sent()) {
        http_response_code(500);
    }

    // Chỉ truyền chi tiết lỗi cho View khi debug=true; production -> null (giấu lỗi).
    render('errors/500', [
        'title'  => '500 Internal Server Error',
        'detail' => $appConfig['debug'] ? (string) $e : null,
    ]);
});

// ---------------------------------------------------------------------------
// 6. Container — các factory "lười", ráp phụ thuộc theo tầng:
//    PDO -> Repository -> Service -> Controller.
//    $db dùng chung một kết nối PDO, chỉ tạo khi thực sự cần.
// ---------------------------------------------------------------------------
$pdo = null;
$db  = function () use (&$pdo, $dbConfig): PDO {
    if ($pdo === null) {
        $pdo = Database::connect($dbConfig);
    }
    return $pdo;
};

$container = [
    HomeController::class        => fn() => new HomeController(),
    HealthController::class      => fn() => new HealthController($dbConfig),
    AuthController::class        => fn() => new AuthController(new AuthService(new UserRepository($db()))),
    DashboardController::class   => fn() => new DashboardController(new DashboardService(new DashboardRepository($db()))),
    PatientController::class     => fn() => new PatientController(new PatientService(new PatientRepository($db()))),
    RegistrationController::class => fn() => new RegistrationController(new PatientService(new PatientRepository($db()))),
    AppointmentController::class => fn() => new AppointmentController(new AppointmentService(new AppointmentRepository($db()))),
];

$resolve = function (string $class) use ($container): object {
    if (!isset($container[$class])) {
        throw new RuntimeException("Container chưa khai báo binding cho {$class}");
    }
    return ($container[$class])();
};

// ---------------------------------------------------------------------------
// 7. Bảng route (METHOD + PATH -> Controller@action).
//    Route đặt theo kebab-case, danh từ số nhiều (Week 8-4, Naming Convention).
// ---------------------------------------------------------------------------
$router = new Router();

$router->get('/',       [HomeController::class, 'index']);
$router->get('/health', [HealthController::class, 'index']);

$router->get('/login',     [AuthController::class, 'login']);
$router->post('/login',    [AuthController::class, 'handleLogin']);
$router->post('/logout',   [AuthController::class, 'logout']);
$router->get('/dashboard', [DashboardController::class, 'index']);

// Đăng ký khám công khai (không cần đăng nhập) + chống spam.
$router->get('/register',  [RegistrationController::class, 'show']);
$router->post('/register', [RegistrationController::class, 'submit']);

// Module Bệnh nhân (quản trị).
$router->get('/patients',         [PatientController::class, 'index']);
$router->get('/patients/create',  [PatientController::class, 'create']);
$router->post('/patients/store',  [PatientController::class, 'store']);
$router->get('/patients/edit',    [PatientController::class, 'edit']);
$router->post('/patients/update', [PatientController::class, 'update']);
$router->post('/patients/delete', [PatientController::class, 'delete']);

// Module Lịch hẹn (quản trị).
$router->get('/appointments',         [AppointmentController::class, 'index']);
$router->get('/appointments/create',  [AppointmentController::class, 'create']);
$router->post('/appointments/store',  [AppointmentController::class, 'store']);
$router->get('/appointments/edit',    [AppointmentController::class, 'edit']);
$router->post('/appointments/update', [AppointmentController::class, 'update']);
$router->post('/appointments/delete', [AppointmentController::class, 'delete']);

// ---------------------------------------------------------------------------
// 8. CSRF guard (Week 4): mọi POST phải kèm _token khớp session, nếu không -> 419.
// ---------------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !csrf_verify($_POST['_token'] ?? null)) {
    http_response_code(419);
    render('errors/419', ['title' => '419 Phiên hết hạn']);
    return;
}

// ---------------------------------------------------------------------------
// 9. Điều phối
// ---------------------------------------------------------------------------
$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/',
    $resolve
);
