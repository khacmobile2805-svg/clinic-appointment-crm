<?php
/** @var int $newPatients */ /** @var int $contactedPatients */
/** @var int $totalAppointments */ /** @var float $revenue */
/** @var array $recentPatients */
?>
<div class="page-head">
    <div>
        <span class="eyebrow">Tổng quan</span>
        <h1 style="margin-top:6px;">Bảng điều khiển</h1>
        <div class="sub">Số liệu cập nhật theo thời gian thực từ cơ sở dữ liệu.</div>
    </div>
    <a class="btn btn--primary" href="/appointments/create">+ Tạo lịch hẹn</a>
</div>

<div class="stats">
    <div class="stat stat--brand">
        <div class="stat__label">Bệnh nhân mới</div>
        <div class="stat__num"><?= (int) $newPatients ?></div>
    </div>
    <div class="stat stat--accent">
        <div class="stat__label">Đã liên hệ</div>
        <div class="stat__num"><?= (int) $contactedPatients ?></div>
    </div>
    <div class="stat stat--brand">
        <div class="stat__label">Lịch hẹn</div>
        <div class="stat__num"><?= (int) $totalAppointments ?></div>
    </div>
    <div class="stat">
        <div class="stat__label">Doanh thu (đ)</div>
        <div class="stat__num"><?= number_format((float) $revenue, 0, ',', '.') ?></div>
    </div>
</div>

<div class="grid-2" style="margin-top:18px;">
    <div class="panel">
        <div class="panel__head" style="display:flex;justify-content:space-between;align-items:center;">
            <h2>Bệnh nhân gần đây</h2>
            <a href="/patients" style="font-size:13.5px;">Xem tất cả</a>
        </div>
        <div class="table-wrap">
            <table class="data">
                <tbody>
                <?php foreach ($recentPatients as $p): ?>
                    <tr>
                        <td style="font-weight:500;"><?= e($p['full_name']) ?></td>
                        <td class="muted mono" style="font-size:13px;"><?= e($p['email']) ?></td>
                        <td><span class="badge badge--<?= e($p['status']) ?>"><?= e(status_label($p['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recentPatients)): ?>
                    <tr><td class="empty">Chưa có bệnh nhân nào.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head"><h2>Cơ chế bảo mật áp dụng</h2></div>
        <ul class="health">
            <li><span class="dot"></span> Phiên đăng nhập hợp lệ</li>
            <li><span class="dot"></span> Kết nối PDO (prepared statements)</li>
            <li><span class="dot"></span> PRG &amp; chống gửi trùng sẵn sàng</li>
            <li><span class="dot"></span> Không nối chuỗi SQL thô</li>
            <li><span class="dot"></span> Flash message dùng một lần</li>
        </ul>
    </div>
</div>
