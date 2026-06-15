<?php

/**
 * AuthService — Trái tim nghiệp vụ của luồng đăng nhập (Week 8-1/8-2).
 * Validate input và xác thực mật khẩu; trả về mảng Result Pattern cho Controller.
 *
 * Cố ý trả về CÙNG một thông báo chung cho "không có user" và "sai mật khẩu",
 * để kẻ tấn công không dò được email nào tồn tại (user enumeration).
 */
class AuthService
{
    public function __construct(private UserRepository $users)
    {
    }

    /**
     * @return array{success:bool, errors:array<string,string>, user:?array}
     */
    public function attempt(string $email, string $password): array
    {
        $email = trim($email);

        // Trình tự kiểm tra: Rỗng -> Định dạng -> Nghiệp vụ (Week 4-2).
        if ($email === '' || $password === '') {
            return $this->fail('Vui lòng nhập email và mật khẩu.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('Email hoặc mật khẩu không đúng.');
        }

        $user = $this->users->findActiveByEmail($email);

        // password_verify so khớp mật khẩu với hash trong DB (Week 6-1: dùng password_hash).
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return $this->fail('Email hoặc mật khẩu không đúng.');
        }

        return ['success' => true, 'errors' => [], 'user' => $user];
    }

    private function fail(string $message): array
    {
        return ['success' => false, 'errors' => ['general' => $message], 'user' => null];
    }
}
