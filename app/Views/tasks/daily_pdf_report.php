<?php
function local_file_uri(string $path): string
{
    return str_replace('\\', '/', $path);
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 100px 50px 70px 50px;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.5;
        }

        .header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 70px;
            border-bottom: 2px solid #344767;
            padding-bottom: 8px;
        }

        .header .brand {
            font-size: 10px;
            color: #8392AB;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header .doctitle {
            font-size: 16px;
            font-weight: bold;
            color: #344767;
        }

        .footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #8392AB;
            border-top: 1px solid #e9ecef;
            padding-top: 6px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #344767;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 1px solid #344767;
            padding-bottom: 4px;
            margin: 18px 0 8px 0;
        }

        .task-block {
            page-break-inside: avoid;
            margin-bottom: 14px;
            border-bottom: 1px solid #f0f2f5;
            padding-bottom: 10px;
        }

        .task-title {
            font-weight: bold;
            font-size: 11.5px;
        }

        .task-meta {
            font-size: 9px;
            color: #8392AB;
            margin-top: 2px;
        }

        .comment {
            margin: 6px 0 0 4px;
            font-size: 9.5px;
            color: #555;
            border-left: 2px solid #eee;
            padding-left: 6px;
        }

        .comment-body img {
            max-width: 150px;
        }

        .comment-body table {
            border-collapse: collapse;
            width: 100%;
            margin: 4px 0;
        }

        .comment-body table td,
        .comment-body table th {
            border: 1px solid #ccc;
            padding: 3px 5px;
            font-size: 9px;
        }

        .comment-body,
        .comment-body * {
            color: #333 !important;
        }

        .attachment-grid {
            margin-top: 6px;
        }

        .attachment-card {
            display: inline-block;
            width: 150px;
            vertical-align: top;
            margin: 0 10px 10px 0;
        }

        .attachment-card img {
            width: 150px;
            height: 110px;
            object-fit: cover;
            border: 1px solid #dfe3e8;
            border-radius: 5px;
        }

        .cat-badge {
            display: inline-block;
            font-size: 8px;
            color: #fff;
            padding: 2px 7px;
            border-radius: 3px;
            margin-top: 4px;
            letter-spacing: .3px;
        }

        .attachment-caption {
            font-size: 8.5px;
            color: #555;
            margin-top: 3px;
            line-height: 1.3;
        }

        .attachment-file-card {
            display: block;
            border: 1px solid #dfe3e8;
            border-radius: 5px;
            padding: 6px 8px;
            margin-bottom: 6px;
        }

        .attachment-file-card a {
            font-size: 9px;
            color: #344767;
            font-weight: bold;
        }

        .summary-box {
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="brand">ProjectTrack &middot; Checklist Harian</div>
        <div class="doctitle">
            Checklist Harian<?= $teamName ? ' - ' . esc($teamName) : ' - Semua Tim' ?>
            &middot; <?= date('d M Y', strtotime($date)) ?>
        </div>
    </div>

    <div class="footer">Dicetak <?= esc($generatedAt) ?> &middot; ProjectTrack</div>

    <?php
    $doneCount  = count(array_filter($tasks, static fn($t) => $t['is_done']));
    $totalCount = count($tasks);
    ?>
    <div class="summary-box">
        <b><?= $doneCount ?> / <?= $totalCount ?></b> task selesai
        <?php if (! $teamName) : ?> &middot; Menampilkan semua tim<?php endif; ?>
    </div>

    <div class="section-title">Checklist</div>

    <?php if (empty($tasks)) : ?>
        <p style="color:#8392AB;">Tidak ada task untuk tanggal ini.</p>
    <?php else : ?>
        <?php foreach ($tasks as $task) : ?>
            <div class="task-block">
                <div class="task-title">
                    [<?= $task['is_done'] ? 'X' : ' ' ?>] <?= esc($task['title']) ?>
                    <?php if (! $teamName && ! empty($task['team_name'])) : ?>
                        <span style="font-weight:normal;color:#8392AB;font-size:9px;"> &middot; <?= esc($task['team_name']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if (! empty($task['description'])) : ?>
                    <div style="font-size:9.5px;color:#666;"><?= esc($task['description']) ?></div>
                <?php endif; ?>
                <?php if ($task['is_done']) : ?>
                    <div class="task-meta">Diceklis oleh <?= esc($task['done_by_name'] ?? '-') ?><?= $task['done_at'] ? ' &middot; ' . date('d M Y H:i', strtotime($task['done_at'])) : '' ?></div>
                <?php endif; ?>

                <?php foreach ($taskComments[$task['id']] ?? [] as $c) : ?>
                    <div class="comment">
                        <i>&raquo; <?= esc($c['fullname'] ?? 'User') ?>, <?= date('d M Y H:i', strtotime($c['created_at'])) ?>:</i>
                        <div class="comment-body"><?= $c['comment'] ?></div>
                    </div>
                <?php endforeach; ?>

                <?php if (! empty($taskEvidences[$task['id']])) : ?>
                    <div class="attachment-grid">
                        <?php foreach ($taskEvidences[$task['id']] as $ev) : ?>
                            <?php if (is_image_mime($ev['mime_type'])) : ?>
                                <div class="attachment-card">
                                    <img src="<?= local_file_uri(FCPATH . 'uploads/evidence/' . $ev['file_name']) ?>">
                                    <div>
                                        <span class="cat-badge" style="background-color: <?= esc($ev['category_color'] ?? '#8392AB') ?>;">
                                            <?= esc($ev['category_name'] ?? 'Tanpa Kategori') ?>
                                        </span>
                                    </div>
                                    <?php if (! empty($ev['caption'])) : ?>
                                        <div class="attachment-caption"><?= esc($ev['caption']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php else : ?>
                                <div class="attachment-file-card">
                                    <span class="cat-badge" style="background-color: <?= esc($ev['category_color'] ?? '#8392AB') ?>;">
                                        <?= esc($ev['category_name'] ?? 'Tanpa Kategori') ?>
                                    </span>
                                    <div style="margin-top:4px;">
                                        <a href="<?= base_url('uploads/evidence/' . $ev['file_name']) ?>"><?= esc($ev['original_name']) ?></a>
                                        <span style="color:#8392AB;font-size:8.5px;"> (<?= format_file_size((int) $ev['file_size']) ?>)</span>
                                    </div>
                                    <?php if (! empty($ev['caption'])) : ?>
                                        <div class="attachment-caption"><?= esc($ev['caption']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>

</html>