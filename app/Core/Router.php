<?php

/**
 * Router — Ánh xạ HTTP METHOD + PATH tới cặp [Lớp Controller, action].
 *
 * Cố ý phân biệt 2 loại lỗi (Week 8 - định tuyến chuẩn):
 *   404 Not Found          -> path không được đăng ký cho BẤT KỲ method nào.
 *   405 Method Not Allowed -> path có tồn tại nhưng không hỗ trợ method hiện tại.
 *
 * Các thao tác thay đổi dữ liệu (create/update/delete) chỉ đăng ký dạng POST,
 * nên gọi bằng GET sẽ nhận 405 (gắn với quy tắc "Xóa bằng POST" - Week 7-1).
 */
class Router
{
    /** @var array<string, array<string, array{0:string,1:string}>> */
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    /**
     * @param callable $resolve function(string $class): object  Dựng một instance controller.
     */
    public function dispatch(string $method, string $uri, callable $resolve): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        // Chuẩn hóa: bỏ dấu "/" ở cuối cho mọi path trừ trang gốc.
        $path = $path === '/' ? '/' : rtrim($path, '/');

        $existsForAnyMethod = isset($this->routes['GET'][$path])
            || isset($this->routes['POST'][$path]);

        if (!$existsForAnyMethod) {
            http_response_code(404);
            render('errors/404', ['title' => '404 Not Found']);
            return;
        }

        // Path có nhưng sai method -> 405, kèm header Allow liệt kê method hợp lệ.
        if (!isset($this->routes[$method][$path])) {
            http_response_code(405);
            header('Allow: ' . implode(', ', $this->allowedMethods($path)));
            render('errors/405', ['title' => '405 Method Not Allowed']);
            return;
        }

        [$class, $action] = $this->routes[$method][$path];
        $controller = $resolve($class);
        $controller->$action();
    }

    /** @return string[] */
    private function allowedMethods(string $path): array
    {
        $allowed = [];
        foreach ($this->routes as $method => $paths) {
            if (isset($paths[$path])) {
                $allowed[] = $method;
            }
        }
        return $allowed;
    }
}
