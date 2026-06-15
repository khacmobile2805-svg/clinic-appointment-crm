<?php

/**
 * DashboardController — Trang tổng quan cần đăng nhập.
 * require_login() kiểm tra phiên + idle timeout trước khi làm bất cứ điều gì.
 */
class DashboardController
{
    public function __construct(private DashboardService $service)
    {
    }

    /** GET /dashboard */
    public function index(): void
    {
        require_login();
        $data = $this->service->getOverview();
        render('dashboard/index', ['title' => 'Dashboard'] + $data);
    }
}
