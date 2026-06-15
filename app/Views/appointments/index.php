<?php
/** @var array $appointments */ /** @var string $keyword */ /** @var string $sort */
/** @var string $direction */ /** @var int $page */ /** @var int $totalPages */
/** @var int $totalItems */ /** @var int $perPage */

if (!function_exists('appt_sort_link')) {
    function appt_sort_link(string $col, string $label, string $keyword, string $sort, string $direction): string
    {
        $nextDir = ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
        $ind = $sort === $col ? '<span class="sort-ind">' . ($direction === 'asc' ? '▲' : '▼') . '</span>' : '';
        $qs = http_build_query(['q' => $keyword, 'sort' => $col, 'direction' => $nextDir]);
        return '<a href="/appointments?' . e($qs) . '">' . e($label) . $ind . '</a>';
    }
}
if (!function_exists('appt_page_url')) {
    function appt_page_url(int $p, string $keyword, string $sort, string $direction): string
    {
        return '/appointments?' . e(http_build_query(['q' => $keyword, 'sort' => $sort, 'direction' => $direction, 'page' => $p]));
    }
}
$from = $totalItems ? (($page - 1) * $perPage + 1) : 0;
$to   = min($page * $perPage, $totalItems);
?>
<div class="page-head">
    <div><span class="eyebrow">Lịch khám</span><h1 style="margin-top:6px;">Lịch hẹn</h1></div>
    <a class="btn btn--primary" href="/appointments/create">+ Tạo lịch hẹn</a>
</div>

<div class="toolbar">
    <form class="search" method="GET" action="/appointments">
        <input class="input" name="q" value="<?= e($keyword) ?>" placeholder="Tìm theo mã, tên bệnh nhân, email">
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
                    <th><?= appt_sort_link('appointment_code', 'Mã', $keyword, $sort, $direction) ?></th>
                    <th><?= appt_sort_link('patient_name', 'Bệnh nhân', $keyword, $sort, $direction) ?></th>
                    <th>Khoa</th>
                    <th><?= appt_sort_link('scheduled_at', 'Lịch khám', $keyword, $sort, $direction) ?></th>
                    <th><?= appt_sort_link('fee', 'Phí', $keyword, $sort, $direction) ?></th>
                    <th><?= appt_sort_link('status', 'Trạng thái', $keyword, $sort, $direction) ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($appointments as $index => $a): ?>
                <tr>
                    <td class="mono muted"><?= $from + $index ?></td>
                    <td class="mono" style="font-weight:500;"><?= e($a['appointment_code']) ?></td>
                    <td><?= e($a['patient_name']) ?></td>
                    <td class="muted"><?= e(department_label($a['department'])) ?></td>
                    <td class="muted mono" style="font-size:13px;"><?= e($a['scheduled_at']) ?></td>
                    <td class="mono"><?= number_format((float) $a['fee'], 0, ',', '.') ?></td>
                    <td><span class="badge badge--<?= e($a['status']) ?>"><?= e(status_label($a['status'])) ?></span></td>
                    <td><a href="/appointments/edit?id=<?= e((string) $a['id']) ?>">Sửa</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($appointments)): ?>
                <tr><td colspan="8" class="empty">Không có lịch hẹn nào khớp tìm kiếm.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="pager-bar">
    <span class="muted" style="font-size:13.5px;">Hiển thị <?= $from ?>–<?= $to ?> trong <?= $totalItems ?> lịch hẹn</span>
    <div class="pager">
        <?php if ($page > 1): ?><a href="<?= appt_page_url($page - 1, $keyword, $sort, $direction) ?>">‹</a><?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i === $page): ?><span class="is-current"><?= $i ?></span>
            <?php else: ?><a href="<?= appt_page_url($i, $keyword, $sort, $direction) ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?><a href="<?= appt_page_url($page + 1, $keyword, $sort, $direction) ?>">›</a><?php endif; ?>
    </div>
</div>
