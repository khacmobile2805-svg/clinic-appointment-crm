<?php

/**
 * PatientController — Controller mỏng cho module Bệnh nhân (Week 8-2).
 * Đọc request -> gọi PatientService -> render hoặc redirect (PRG). Không SQL ở đây.
 * Mọi action đều yêu cầu đã đăng nhập.
 */
class PatientController
{
    public function __construct(private PatientService $service)
    {
    }

    /** GET /patients */
    public function index(): void
    {
        require_login();
        $data = $this->service->getPatientList($_GET);
        render('patients/index', ['title' => 'Quản lý bệnh nhân'] + $data);
    }

    /** GET /patients/create */
    public function create(): void
    {
        require_login();
        render('patients/create', ['title' => 'Thêm bệnh nhân', 'errors' => []]);
    }

    /** POST /patients/store */
    public function store(): void
    {
        require_login();
        $result = $this->service->createPatient($_POST);

        if (!$result['success']) {
            // Lỗi -> ở lại, giữ old input để sửa (Empathetic UX - Week 4-2).
            $_SESSION['old'] = $_POST;
            render('patients/create', ['title' => 'Thêm bệnh nhân', 'errors' => $result['errors']]);
            return;
        }

        // PRG: thành công thì redirect (Week 4-3).
        flash('success', 'Đã thêm bệnh nhân thành công.');
        redirect('/patients');
    }

    /** GET /patients/edit?id=.. */
    public function edit(): void
    {
        require_login();
        $id = (int) ($_GET['id'] ?? 0);
        $patient = $this->service->getPatientForEdit($id);

        if (!$patient) {
            flash('error', 'Không tìm thấy bệnh nhân.');
            redirect('/patients');
        }

        render('patients/edit', ['title' => 'Sửa bệnh nhân', 'patient' => $patient, 'errors' => []]);
    }

    /** POST /patients/update */
    public function update(): void
    {
        require_login();
        $id = (int) ($_POST['id'] ?? 0);
        $result = $this->service->updatePatient($id, $_POST);

        if (!$result['success']) {
            $patient = $this->service->getPatientForEdit($id) ?? ['id' => $id];
            $_SESSION['old'] = $_POST;
            render('patients/edit', ['title' => 'Sửa bệnh nhân', 'patient' => $patient, 'errors' => $result['errors']]);
            return;
        }

        flash('success', 'Đã cập nhật bệnh nhân.');
        redirect('/patients');
    }

    /** POST /patients/delete — Xóa BẮT BUỘC bằng POST (Week 7-1), chống Bot/Crawler cào link. */
    public function delete(): void
    {
        require_login();
        $id = (int) ($_POST['id'] ?? 0);
        $this->service->deletePatient($id);

        flash('success', 'Đã xóa bệnh nhân.');
        redirect('/patients');
    }
}
