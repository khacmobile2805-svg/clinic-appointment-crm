<?php
/** @var array $errors */ /** @var array $departments */
$top = $errors['general'] ?? ($errors['appointment_code'] ?? null);
$statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
?>
<div class="page-head">
    <div><span class="eyebrow">Lịch khám</span><h1 style="margin-top:6px;">Tạo lịch hẹn</h1></div>
    <a class="btn btn--ghost" href="/appointments">← Danh sách</a>
</div>

<?php if ($top): ?><div class="flash flash--error"><?= e($top) ?> Dữ liệu cũ vẫn được giữ lại để sửa.</div><?php endif; ?>

<div class="form-grid">
    <div class="card">
        <form method="POST" action="/appointments/store">
            <?= csrf_field() ?>
            <div class="field">
                <label class="label" for="appointment_code">Mã lịch hẹn</label>
                <input class="input <?= !empty($errors['appointment_code']) ? 'has-error' : '' ?>" id="appointment_code" name="appointment_code" value="<?= e(old('appointment_code')) ?>">
                <?php if (!empty($errors['appointment_code'])): ?><div class="field-error"><?= e($errors['appointment_code']) ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label class="label" for="patient_name">Tên bệnh nhân</label>
                <input class="input <?= !empty($errors['patient_name']) ? 'has-error' : '' ?>" id="patient_name" name="patient_name" value="<?= e(old('patient_name')) ?>">
                <?php if (!empty($errors['patient_name'])): ?><div class="field-error"><?= e($errors['patient_name']) ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label class="label" for="patient_email">Email bệnh nhân (tùy chọn)</label>
                <input class="input <?= !empty($errors['patient_email']) ? 'has-error' : '' ?>" id="patient_email" name="patient_email" value="<?= e(old('patient_email')) ?>">
                <?php if (!empty($errors['patient_email'])): ?><div class="field-error"><?= e($errors['patient_email']) ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label class="label" for="department">Khoa</label>
                <select class="select" id="department" name="department">
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= e($d) ?>" <?= old('department', 'General') === $d ? 'selected' : '' ?>><?= e(department_label($d)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label class="label" for="scheduled_at">Thời gian khám</label>
                <small class="muted" style="display:block;margin-bottom:6px;font-size:13px;">Vui lòng chọn ngày và giờ dự kiến bệnh nhân sẽ đến khám.</small>
                <input class="input <?= !empty($errors['scheduled_at']) ? 'has-error' : '' ?>" id="scheduled_at" type="datetime-local" name="scheduled_at" value="<?= e(old('scheduled_at')) ?>">
                <?php if (!empty($errors['scheduled_at'])): ?><div class="field-error"><?= e($errors['scheduled_at']) ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label class="label" for="fee">Phí khám (đ)</label>
                <small class="muted" style="display:block;margin-bottom:6px;font-size:13px;">Nhập số tiền bằng VNĐ (không nhập dấu phẩy). Ví dụ: 300000 cho 300.000đ.</small>
                <input class="input <?= !empty($errors['fee']) ? 'has-error' : '' ?>" id="fee" type="number" name="fee" min="0" step="1000" placeholder="Ví dụ: 300000" value="<?= e(old('fee')) ?>">
                <?php if (!empty($errors['fee'])): ?><div class="field-error"><?= e($errors['fee']) ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label class="label" for="status">Trạng thái</label>
                <select class="select" id="status" name="status">
                    <?php foreach ($statuses as $val): ?>
                        <option value="<?= $val ?>" <?= old('status', 'pending') === $val ? 'selected' : '' ?>><?= e(status_label($val)) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['status'])): ?><div class="field-error"><?= e($errors['status']) ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label class="label" for="note">Ghi chú</label>
                <textarea class="textarea" id="note" name="note"><?= e(old('note')) ?></textarea>
            </div>
            <div class="form-actions">
                <button class="btn btn--primary" type="submit">Lưu lịch hẹn</button>
                <a class="btn btn--ghost" href="/appointments">Hủy</a>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Quy tắc lịch hẹn</h2>
        <ul class="rules" style="margin-top:10px;">
            <li><span class="mono">appointment_code</span> bắt buộc + duy nhất</li>
            <li>Tên bệnh nhân bắt buộc</li>
            <li>Email đúng định dạng nếu có nhập</li>
            <li>Thời gian khám bắt buộc</li>
            <li>Phí khám &ge; 0</li>
            <li>Trạng thái trong whitelist</li>
            <li>PRG sau khi tạo thành công</li>
        </ul>
    </div>
</div>
