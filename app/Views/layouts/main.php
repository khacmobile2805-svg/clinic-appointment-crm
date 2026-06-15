<?php
/** @var string $title */
/** @var string $content */
$loggedIn = !empty($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? '') ?> · <?= e(config('name', 'Clinic CRM')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
<?php if ($loggedIn): ?>
    <div class="app-shell">
        <aside class="sidebar"><?php partial('nav'); ?></aside>
        <div class="main">
            <main class="content">
                <?php partial('flash'); ?>
                <?= $content ?>
            </main>
        </div>
    </div>
<?php else: ?>
    <div class="auth-shell">
        <div style="width:100%;max-width:880px;">
            <?php partial('flash'); ?>
            <?= $content ?>
        </div>
    </div>
<?php endif; ?>
</body>
</html>
