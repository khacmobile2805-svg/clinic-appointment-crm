<?php
/** @var array $patient */ /** @var array $errors */
$top = $errors['general'] ?? ($errors['email'] ?? null);
$genders = ['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác'];
$statuses = ['new', 'contacted', 'scheduled', 'inactive'];
?>
<div class="page-head">
    <div>
        <span class="eyebrow">Hồ sơ · <span class="mono">#<?= e((string) ($patient['id'] ?? '')) ?></span></span>
        <h1 style="margin-top:6px;">Sửa bệnh nhân</h1>
    </div>
    <a class="btn btn--ghost" href="/patients">← Danh sách</a>
</div>

<?php if ($top): ?><div class="flash flash--error"><?= e($top) ?></div><?php endif; ?>

<div class="card" style="max-width:680px;">
    <form method="POST" action="/patients/update">
            <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e((string) ($patient['id'] ?? '')) ?>">
        <div class="field">
            <label class="label" for="full_name">Họ tên</label>
            <input class="input <?= !empty($errors['full_name']) ? 'has-error' : '' ?>" id="full_name" name="full_name" value="<?= e(old('full_name', $patient['full_name'] ?? '')) ?>">
            <?php if (!empty($errors['full_name'])): ?><div class="field-error"><?= e($errors['full_name']) ?></div><?php endif; ?>
        </div>
        <div class="field">
            <label class="label" for="email">Email</label>
            <input class="input <?= !empty($errors['email']) ? 'has-error' : '' ?>" id="email" name="email" value="<?= e(old('email', $patient['email'] ?? '')) ?>">
            <?php if (!empty($errors['email'])): ?><div class="field-error"><?= e($errors['email']) ?></div><?php endif; ?>
        </div>
        <div class="field">
            <label class="label" for="phone">Số điện thoại</label>
            <input class="input" id="phone" name="phone" value="<?= e(old('phone', $patient['phone'] ?? '')) ?>">
        </div>
        <div class="field">
            <label class="label" for="gender">Giới tính</label>
            <select class="select" id="gender" name="gender">
                <?php foreach ($genders as $val => $lbl): ?>
                    <option value="<?= $val ?>" <?= old('gender', $patient['gender'] ?? 'other') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label class="label" for="status">Trạng thái</label>
            <select class="select" id="status" name="status">
                <?php foreach ($statuses as $val): ?>
                    <option value="<?= $val ?>" <?= old('status', $patient['status'] ?? 'new') === $val ? 'selected' : '' ?>><?= e(status_label($val)) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['status'])): ?><div class="field-error"><?= e($errors['status']) ?></div><?php endif; ?>
        </div>
        <div class="field">
            <label class="label" for="note">Ghi chú</label>
            <textarea class="textarea" id="note" name="note"><?= e(old('note', $patient['note'] ?? '')) ?></textarea>
        </div>
        <div class="form-actions">
            <button class="btn btn--primary" type="submit">Cập nhật</button>
            <a class="btn btn--ghost" href="/patients">Quay lại</a>
        </div>
    </form>

    <!-- Form Xóa là một form POST RIÊNG: thao tác thay đổi dữ liệu không bao giờ dùng GET (Week 7-1). -->
    <form method="POST" action="/patients/delete" style="margin-top:14px;border-top:1px solid var(--line);padding-top:14px;"
          onsubmit="return confirm('Xóa bệnh nhân này?');">
            <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e((string) ($patient['id'] ?? '')) ?>">
        <button class="btn btn--danger" type="submit">Xóa bệnh nhân</button>
    </form>
</div>
