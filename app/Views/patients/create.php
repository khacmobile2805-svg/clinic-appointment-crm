<?php
/** @var array $errors */
$top = $errors['general'] ?? ($errors['email'] ?? null);
$genders = ['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác'];
$statuses = ['new', 'contacted', 'scheduled', 'inactive'];
?>
<div class="page-head">
    <div><span class="eyebrow">Hồ sơ</span><h1 style="margin-top:6px;">Thêm bệnh nhân</h1></div>
    <a class="btn btn--ghost" href="/patients">← Danh sách</a>
</div>

<?php if ($top): ?>
    <div class="flash flash--error"><?= e($top) ?> Dữ liệu cũ vẫn được giữ lại để sửa.</div>
<?php endif; ?>

<div class="form-grid">
    <div class="card">
        <form method="POST" action="/patients/store">
            <?= csrf_field() ?>
            <div class="field">
                <label class="label" for="full_name">Họ tên</label>
                <input class="input <?= !empty($errors['full_name']) ? 'has-error' : '' ?>" id="full_name" name="full_name" value="<?= e(old('full_name')) ?>">
                <?php if (!empty($errors['full_name'])): ?><div class="field-error"><?= e($errors['full_name']) ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label class="label" for="email">Email</label>
                <input class="input <?= !empty($errors['email']) ? 'has-error' : '' ?>" id="email" name="email" value="<?= e(old('email')) ?>">
                <?php if (!empty($errors['email'])): ?><div class="field-error"><?= e($errors['email']) ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label class="label" for="phone">Số điện thoại</label>
                <input class="input" id="phone" name="phone" value="<?= e(old('phone')) ?>">
            </div>
            <div class="field">
                <label class="label" for="gender">Giới tính</label>
                <select class="select" id="gender" name="gender">
                    <?php foreach ($genders as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= old('gender', 'other') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label class="label" for="status">Trạng thái</label>
                <select class="select" id="status" name="status">
                    <?php foreach ($statuses as $val): ?>
                        <option value="<?= $val ?>" <?= old('status', 'new') === $val ? 'selected' : '' ?>><?= e(status_label($val)) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['status'])): ?><div class="field-error"><?= e($errors['status']) ?></div><?php endif; ?>
            </div>
            <div class="field">
                <label class="label" for="note">Ghi chú</label>
                <textarea class="textarea" id="note" name="note"><?= e(old('note')) ?></textarea>
            </div>
            <div class="form-actions">
                <button class="btn btn--primary" type="submit">Lưu bệnh nhân</button>
                <a class="btn btn--ghost" href="/patients">Hủy</a>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Luồng an toàn</h2>
        <ol class="rules" style="margin-top:10px;">
            <li>Đọc POST an toàn (trim)</li>
            <li>Validate phía server</li>
            <li>Honeypot / rate limit (form công khai)</li>
            <li>Prepared INSERT</li>
            <li>Bắt trùng (DuplicateRecordException)</li>
            <li>Báo lỗi thân thiện + giữ old input</li>
        </ol>
    </div>
</div>
