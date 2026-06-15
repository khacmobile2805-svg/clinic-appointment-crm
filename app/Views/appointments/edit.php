<?php
/** @var array $appointment */ /** @var array $errors */ /** @var array $departments */
$top = $errors['general'] ?? ($errors['appointment_code'] ?? null);
$statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
// DB lưu dạng "Y-m-d H:i:s"; input datetime-local cần "Y-m-d\TH:i" nên chuyển đổi tại đây.
$schedDefault = '';
if (!empty($appointment['scheduled_at'])) {
    $ts = strtotime($appointment['scheduled_at']);
    $schedDefault = $ts ? date('Y-m-d\TH:i', $ts) : '';
}
$feeDefault = isset($appointment['fee']) ? (string) (0 + $appointment['fee']) : '';
?>
<div class="page-head">
    <div>
        <span class="eyebrow">Lịch khám · <span class="mono">#<?= e((string) ($appointment['id'] ?? '')) ?></span></span>
        <h1 style="margin-top:6px;">Sửa lịch hẹn</h1>
    </div>
    <a class="btn btn--ghost" href="/appointments">← Danh sách</a>
</div>

<?php if ($top): ?><div class="flash flash--error"><?= e($top) ?></div><?php endif; ?>

<div class="card" style="max-width:720px;">
    <form method="POST" action="/appointments/update">
            <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e((string) ($appointment['id'] ?? '')) ?>">
        <div class="field">
            <label class="label" for="appointment_code">Mã lịch hẹn</label>
            <input class="input <?= !empty($errors['appointment_code']) ? 'has-error' : '' ?>" id="appointment_code" name="appointment_code" value="<?= e(old('appointment_code', $appointment['appointment_code'] ?? '')) ?>">
            <?php if (!empty($errors['appointment_code'])): ?><div class="field-error"><?= e($errors['appointment_code']) ?></div><?php endif; ?>
        </div>
        <div class="field">
            <label class="label" for="patient_name">Tên bệnh nhân</label>
            <input class="input <?= !empty($errors['patient_name']) ? 'has-error' : '' ?>" id="patient_name" name="patient_name" value="<?= e(old('patient_name', $appointment['patient_name'] ?? '')) ?>">
            <?php if (!empty($errors['patient_name'])): ?><div class="field-error"><?= e($errors['patient_name']) ?></div><?php endif; ?>
        </div>
        <div class="field">
            <label class="label" for="patient_email">Email bệnh nhân (tùy chọn)</label>
            <input class="input <?= !empty($errors['patient_email']) ? 'has-error' : '' ?>" id="patient_email" name="patient_email" value="<?= e(old('patient_email', $appointment['patient_email'] ?? '')) ?>">
            <?php if (!empty($errors['patient_email'])): ?><div class="field-error"><?= e($errors['patient_email']) ?></div><?php endif; ?>
        </div>
        <div class="field">
            <label class="label" for="department">Khoa</label>
            <select class="select" id="department" name="department">
                <?php foreach ($departments as $d): ?>
                    <option value="<?= e($d) ?>" <?= old('department', $appointment['department'] ?? 'General') === $d ? 'selected' : '' ?>><?= e(department_label($d)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label class="label" for="scheduled_at">Thời gian khám</label>
            <small class="muted" style="display:block;margin-bottom:6px;font-size:13px;">Vui lòng chọn ngày và giờ dự kiến bệnh nhân sẽ đến khám.</small>
            <input class="input <?= !empty($errors['scheduled_at']) ? 'has-error' : '' ?>" id="scheduled_at" type="datetime-local" name="scheduled_at" value="<?= e(old('scheduled_at', $schedDefault)) ?>">
            <?php if (!empty($errors['scheduled_at'])): ?><div class="field-error"><?= e($errors['scheduled_at']) ?></div><?php endif; ?>
        </div>
        <div class="field">
            <label class="label" for="fee">Phí khám (đ)</label>
            <small class="muted" style="display:block;margin-bottom:6px;font-size:13px;">Nhập số tiền bằng VNĐ (không nhập dấu phẩy). Ví dụ: 300000 cho 300.000đ.</small>
            <input class="input <?= !empty($errors['fee']) ? 'has-error' : '' ?>" id="fee" type="number" name="fee" min="0" step="1000" placeholder="Ví dụ: 300000" value="<?= e(old('fee', $feeDefault)) ?>">
            <?php if (!empty($errors['fee'])): ?><div class="field-error"><?= e($errors['fee']) ?></div><?php endif; ?>
        </div>
        <div class="field">
            <label class="label" for="status">Trạng thái</label>
            <select class="select" id="status" name="status">
                <?php foreach ($statuses as $val): ?>
                    <option value="<?= $val ?>" <?= old('status', $appointment['status'] ?? 'pending') === $val ? 'selected' : '' ?>><?= e(status_label($val)) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['status'])): ?><div class="field-error"><?= e($errors['status']) ?></div><?php endif; ?>
        </div>
        <div class="field">
            <label class="label" for="note">Ghi chú</label>
            <textarea class="textarea" id="note" name="note"><?= e(old('note', $appointment['note'] ?? '')) ?></textarea>
        </div>
        <div class="form-actions">
            <button class="btn btn--primary" type="submit">Cập nhật</button>
            <a class="btn btn--ghost" href="/appointments">Quay lại</a>
        </div>
    </form>

    <!-- Form Xóa là form POST RIÊNG: thao tác thay đổi dữ liệu không bao giờ dùng GET (Week 7-1). -->
    <form method="POST" action="/appointments/delete" style="margin-top:14px;border-top:1px solid var(--line);padding-top:14px;"
          onsubmit="return confirm('Xóa lịch hẹn này?');">
            <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e((string) ($appointment['id'] ?? '')) ?>">
        <button class="btn btn--danger" type="submit">Xóa lịch hẹn</button>
    </form>
</div>
