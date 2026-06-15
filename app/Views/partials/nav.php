<?php
// Đánh dấu mục đang mở dựa trên path hiện tại.
$cur = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$active = function (string $prefix) use ($cur): string {
    if ($prefix === '/dashboard') {
        return $cur === '/dashboard' ? 'is-active' : '';
    }
    return str_starts_with($cur, $prefix) ? 'is-active' : '';
};
$name = (string) ($_SESSION['user_name'] ?? '');
$initial = $name !== '' ? mb_strtoupper(mb_substr($name, 0, 1)) : '?';
?>
<div class="nav">
    <a class="nav__brand" href="/dashboard">
        <span class="nav__logo">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        </span>
        <span>Clinic CRM</span>
    </a>

    <nav class="nav__menu">
        <a class="nav__link <?= $active('/dashboard') ?>" href="/dashboard">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
            Tổng quan
        </a>
        <a class="nav__link <?= $active('/patients') ?>" href="/patients">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0M16 11h5M18.5 8.5v5"/></svg>
            Bệnh nhân
        </a>
        <a class="nav__link <?= $active('/appointments') ?>" href="/appointments">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="4.5" width="17" height="16" rx="2.5"/><path d="M3.5 9h17M8 3v3M16 3v3M9 14l2 2 4-4"/></svg>
            Lịch hẹn
        </a>
    </nav>

    <div class="nav__foot">
        <div class="nav__user">
            <span class="nav__avatar"><?= e($initial) ?></span>
            <div>
                <div class="nav__name"><?= e($name) ?></div>
                <div class="nav__role"><?= e($_SESSION['user_role'] ?? '') ?></div>
            </div>
        </div>
        <form method="POST" action="/logout">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn--ghost btn--block">Đăng xuất</button>
        </form>
    </div>
</div>
