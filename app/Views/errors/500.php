<?php /** @var ?string $detail */ ?>
<div class="errbox card">
    <div class="code">500</div>
    <h1 style="margin-top:6px;">Lỗi hệ thống</h1>
    <p class="muted" style="margin-top:8px;">Đã có lỗi xảy ra trong quá trình xử lý. Vui lòng thử lại sau.</p>
    <?php if (!empty($detail)): ?>
        <p class="muted" style="font-size:12px;margin-top:10px;">(Chi tiết chỉ hiển thị khi debug = true)</p>
        <pre style="text-align:left;white-space:pre-wrap;background:#0d2622;color:#cfe3de;padding:14px;border-radius:12px;overflow:auto;font-size:12px;font-family:var(--mono);"><?= e($detail) ?></pre>
    <?php endif; ?>
    <div class="form-actions" style="justify-content:center;"><a class="btn btn--primary" href="/">Về trang chủ</a></div>
</div>
