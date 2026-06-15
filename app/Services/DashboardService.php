<?php

/**
 * DashboardService — Gom dữ liệu tổng quan cho View dashboard.
 * Controller chỉ gọi service này; mọi truy vấn nằm ở DashboardRepository.
 */
class DashboardService
{
    public function __construct(private DashboardRepository $repo)
    {
    }

    public function getOverview(): array
    {
        return [
            'newPatients'       => $this->repo->patientCountByStatus('new'),
            'contactedPatients' => $this->repo->patientCountByStatus('contacted'),
            'totalAppointments' => $this->repo->totalAppointments(),
            'revenue'           => $this->repo->revenue(),
            'recentPatients'    => $this->repo->recentPatients(5),
        ];
    }
}
