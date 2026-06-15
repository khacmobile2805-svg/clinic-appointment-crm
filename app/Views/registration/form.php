<?php /** @var array $errors */ ?>
<div class="card" style="width:min(520px,100%);margin:0 auto;">
    <span class="eyebrow">Phòng khám</span>
    <h1 style="margin-top:8px;">Đăng ký khám</h1>
    <p class="muted" style="font-size:14px;margin-top:6px;">Để lại thông tin, phòng khám sẽ liên hệ xác nhận lịch.</p>

    <?php if (!empty($errors['general'])): ?>
        <div class="flash flash--error" style="margin-top:16px;"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" action="/register" style="margin-top:14px;">
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
            <label class="label" for="note">Triệu chứng / ghi chú</label>
            <textarea class="textarea" id="note" name="note"><?= e(old('note')) ?></textarea>
        </div>

        <!-- Honeypot: ẩn với người dùng; bot điền vào sẽ bị từ chối (Bẫy Không Gian - Week 4-4). -->
        <div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
            <label for="website">Website</label>
            <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        <button class="btn btn--primary btn--block" type="submit" style="margin-top:16px;">Gửi đăng ký</button>
    </form>
    <p class="demo-hint"><a href="/login">Nhân viên đăng nhập →</a></p>
</div>
