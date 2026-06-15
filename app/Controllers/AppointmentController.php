<?php

/**
 * AppointmentController — Controller mỏng cho module Lịch hẹn (Week 8-2).
 * Cùng hình dáng với PatientController, khác module + quy tắc. Không SQL ở đây.
 */
class AppointmentController
{
    public function __construct(private AppointmentService $service)
    {
    }

    /** GET /appointments */
    public function index(): void
    {
        require_login();
        $data = $this->service->getAppointmentList($_GET);
        render('appointments/index', ['title' => 'Quản lý lịch hẹn'] + $data);
    }

    /** GET /appointments/create */
    public function create(): void
    {
        require_login();
        render('appointments/create', [
            'title'       => 'Tạo lịch hẹn',
            'errors'      => [],
            'departments' => $this->service->departments(),
        ]);
    }

    /** POST /appointments/store */
    public function store(): void
    {
        require_login();
        $result = $this->service->createAppointment($_POST);

        if (!$result['success']) {
            $_SESSION['old'] = $_POST;
            render('appointments/create', [
                'title'       => 'Tạo lịch hẹn',
                'errors'      => $result['errors'],
                'departments' => $this->service->departments(),
            ]);
            return;
        }

        flash('success', 'Đã tạo lịch hẹn thành công.');
        redirect('/appointments');
    }

    /** GET /appointments/edit?id=.. */
    public function edit(): void
    {
        require_login();
        $id = (int) ($_GET['id'] ?? 0);
        $appointment = $this->service->getAppointmentForEdit($id);

        if (!$appointment) {
            flash('error', 'Không tìm thấy lịch hẹn.');
            redirect('/appointments');
        }

        render('appointments/edit', [
            'title'       => 'Sửa lịch hẹn',
            'appointment' => $appointment,
            'errors'      => [],
            'departments' => $this->service->departments(),
        ]);
    }

    /** POST /appointments/update */
    public function update(): void
    {
        require_login();
        $id = (int) ($_POST['id'] ?? 0);
        $result = $this->service->updateAppointment($id, $_POST);

        if (!$result['success']) {
            $appointment = $this->service->getAppointmentForEdit($id) ?? ['id' => $id];
            $_SESSION['old'] = $_POST;
            render('appointments/edit', [
                'title'       => 'Sửa lịch hẹn',
                'appointment' => $appointment,
                'errors'      => $result['errors'],
                'departments' => $this->service->departments(),
            ]);
            return;
        }

        flash('success', 'Đã cập nhật lịch hẹn.');
        redirect('/appointments');
    }

    /** POST /appointments/delete — Xóa bằng POST (Week 7-1). */
    public function delete(): void
    {
        require_login();
        $id = (int) ($_POST['id'] ?? 0);
        $this->service->deleteAppointment($id);

        flash('success', 'Đã xóa lịch hẹn.');
        redirect('/appointments');
    }
}
