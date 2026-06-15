<?php /** @var array $errors */ ?>
<div class="auth-shell" style="min-height:auto;padding:0;">
<div class="login">
    <div class="login__brand">
        <span class="mark">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        </span>
        <h2>Clinic Appointment CRM</h2>
        <p>Cổng quản trị dành cho lễ tân và nhân viên phòng khám.</p>
        <div class="pts">
            <span>› Phiên đăng nhập được làm mới sau khi xác thực</span>
            <span>› Mọi thao tác ghi đều bảo vệ và kiểm tra</span>
            <span>› Dữ liệu hồ sơ tra cứu tức thì</span>
        </div>
    </div>
    <div class="login__form">
        <h1>Đăng nhập</h1>
        <p class="muted" style="font-size:13.5px;margin-top:4px;">Nhập thông tin để vào trang quản trị.</p>

        <?php if (!empty($errors['general'])): ?>
            <div class="flash flash--error" style="margin-top:16px;"><?= e($errors['general']) ?></div>
        <?php endif; ?>

        <form method="POST" action="/login" style="margin-top:14px;">
            <?= csrf_field() ?>
            <div class="field">
                <label class="label" for="email">Email</label>
                <input class="input" id="email" type="email" name="email" value="<?= e(old('email')) ?>" autofocus>
            </div>
            <div class="field">
                <label class="label" for="password">Mật khẩu</label>
                <input class="input" id="password" type="password" name="password">
            </div>
            <button class="btn btn--primary btn--block" type="submit" style="margin-top:16px;">Đăng nhập</button>
        </form>

        <p class="demo-hint">Tài khoản demo: <span class="mono">admin@clinic.test</span> / <span class="mono">123456</span></p>
    </div>
</div>
</div>
