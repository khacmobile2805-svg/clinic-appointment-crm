<?php
/** @var array $patients */ /** @var string $keyword */ /** @var string $sort */
/** @var string $direction */ /** @var int $page */ /** @var int $totalPages */
/** @var int $totalItems */ /** @var int $perPage */

if (!function_exists('patient_sort_link')) {
    function patient_sort_link(string $col, string $label, string $keyword, string $sort, string $direction): string
    {
        $nextDir = ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
        $ind = $sort === $col ? '<span class="sort-ind">' . ($direction === 'asc' ? '▲' : '▼') . '</span>' : '';
        $qs = http_build_query(['q' => $keyword, 'sort' => $col, 'direction' => $nextDir]);
        return '<a href="/patients?' . e($qs) . '">' . e($label) . $ind . '</a>';
    }
}
if (!function_exists('patient_page_url')) {
    function patient_page_url(int $p, string $keyword, string $sort, string $direction): string
    {
        return '/patients?' . e(http_build_query(['q' => $keyword, 'sort' => $sort, 'direction' => $direction, 'page' => $p]));
    }
}
$from = $totalItems ? (($page - 1) * $perPage + 1) : 0;
$to   = min($page * $perPage, $totalItems);
?>
<div class="page-head">
    <div>
        <span class="eyebrow">Hồ sơ</span>
        <h1 style="margin-top:6px;">Bệnh nhân</h1>
    </div>
    <a class="btn btn--primary" href="/patients/create">+ Thêm bệnh nhân</a>
</div>

<div class="toolbar">
    <form class="search" method="GET" action="/patients">
        <input class="input" name="q" value="<?= e($keyword) ?>" placeholder="Tìm theo họ tên, email, số điện thoại">
        <button class="btn btn--ghost" type="submit">Tìm kiếm</button>
    </form>
    <span class="note">Đang sắp xếp theo: <strong><?= e($sort) ?></strong> (<?= $direction === 'asc' ? 'Tăng dần' : 'Giảm dần' ?>)</span>
</div>

<div class="panel">
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>STT</th>
                    <th><?= patient_sort_link('full_name', 'Họ tên', $keyword, $sort, $direction) ?></th>
                    <th><?= patient_sort_link('email', 'Email', $keyword, $sort, $direction) ?></th>
                    <th>SĐT</th>
                    <th><?= patient_sort_link('status', 'Trạng thái', $keyword, $sort, $direction) ?></th>
                    <th><?= patient_sort_link('created_at', 'Ngày tạo', $keyword, $sort, $direction) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($patients as $index => $p): ?>
                <tr>
                    <td class="mono muted"><?= $from + $index ?></td>
                    <td style="font-weight:500;"><?= e($p['full_name']) ?></td>
                    <td class="muted mono" style="font-size:13px;"><?= e($p['email']) ?></td>
                    <td class="mono" style="font-size:13px;"><?= e($p['phone'] ?? '') ?></td>
                    <td><span class="badge badge--<?= e($p['status']) ?>"><?= e(status_label($p['status'])) ?></span></td>
                    <td class="muted mono" style="font-size:13px;"><?= e($p['created_at']) ?></td>
                    <td><a href="/patients/edit?id=<?= e((string) $p['id']) ?>">Sửa</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($patients)): ?>
                <tr><td colspan="7" class="empty">Không có bệnh nhân nào khớp tìm kiếm.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="pager-bar">
    <span class="muted" style="font-size:13.5px;">Hiển thị <?= $from ?>–<?= $to ?> trong <?= $totalItems ?> bệnh nhân</span>
    <div class="pager">
        <?php if ($page > 1): ?><a href="<?= patient_page_url($page - 1, $keyword, $sort, $direction) ?>">‹</a><?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i === $page): ?><span class="is-current"><?= $i ?></span>
            <?php else: ?><a href="<?= patient_page_url($i, $keyword, $sort, $direction) ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?><a href="<?= patient_page_url($page + 1, $keyword, $sort, $direction) ?>">›</a><?php endif; ?>
    </div>
</div>
