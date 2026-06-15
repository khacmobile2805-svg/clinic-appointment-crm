<?php

/**
 * HomeController — GET / : trang giới thiệu công khai.
 * Nếu đã đăng nhập thì đưa thẳng vào dashboard.
 */
class HomeController
{
    public function index(): void
    {
        if (!empty($_SESSION['user_id'])) {
            redirect('/dashboard');
        }
        render('home/index', ['title' => 'Trang chủ']);
    }
}
