<?php if ($msg = get_flash('success')): ?>
    <div class="flash flash--success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = get_flash('error')): ?>
    <div class="flash flash--error"><?= e($msg) ?></div>
<?php endif; ?>
