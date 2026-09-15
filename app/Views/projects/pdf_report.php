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

        table.info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        table.info td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 10.5px;
        }

        table.info td.label {
            color: #8392AB;
            width: 100px;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            color: #fff;
            font-size: 9px;
        }

        .progress-bg {
            background: #e9ecef;
            height: 8px;
            border-radius: 4px;
            width: 200px;
        }

        .progress-fill {
            background: #344767;
            height: 8px;
            border-radius: 4px;
        }

        .milestone {
            margin-bottom: 6px;
            padding-left: 14px;
            border-left: 2px solid #e9ecef;
        }

        .milestone .dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-right: 4px;
        }

        .task-title {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .subtask {
            margin-left: 18px;
        }

        .comment {
            margin-left: 18px;
            font-size: 9.5px;
            color: #555;
            border-left: 2px solid #eee;
            padding-left: 6px;
            margin-top: 2px;
            margin-bottom: 4px;
        }

        .attachment-grid {
            margin-top: 6px;
        }

        .attachment-card {
            display: inline-block;
            width: 230px;
            vertical-align: top;
            margin: 0 14px 18px 0;
        }

        .attachment-card img {
            width: 230px;
            height: 175px;
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
            margin-top: 5px;
            letter-spacing: .3px;
        }

        .attachment-caption {
            font-size: 8.5px;
            color: #555;
            margin-top: 4px;
            line-height: 1.3;
        }

        .attachment-file-card {
            display: block;
            border: 1px solid #dfe3e8;
            border-radius: 5px;
            padding: 8px 10px;
            margin-bottom: 8px;
        }

        .attachment-file-card a {
            font-size: 9.5px;
            color: #344767;
            font-weight: bold;
        }

        .box {
            border: 1px solid #e9ecef;
            padding: 8px 10px;
            border-radius: 4px;
            font-size: 10.5px;
            margin-bottom: 8px;
        }

        .task-block {
            page-break-inside: avoid;
            margin-bottom: 10px;
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
    </style>
</head>

<body>

    <div class="header">
        <div class="brand">ProjectTrack &middot; Laporan Project</div>
        <div class="doctitle"><?= esc($project['project_code']) ?> - <?= esc($project['title']) ?></div>
    </div>

    <div class="footer">Dicetak <?= esc($generatedAt) ?> &middot; ProjectTrack</div>

    <table class="info">
        <tr>
            <td class="label">Tim</td>
            <td><?= esc($project['team_name'] ?? '-') ?></td>
            <td class="label">Status</td>
            <td><span class="badge" style="background-color: <?= esc($project['status_color'] ?? '#8392AB') ?>"><?= esc($project['status_name'] ?? '-') ?></span></td>
        </tr>
        <tr>
            <td class="label">Area</td>
            <td><?= esc($project['area_name'] ?? '-') ?></td>
            <td class="label">Prioritas</td>
            <td><span class="badge" style="background-color: <?= esc($project['priority_color'] ?? '#8392AB') ?>"><?= esc($project['priority_name'] ?? '-') ?></span></td>
        </tr>
        <tr>
            <td class="label">Kategori</td>
            <td><?= esc($project['category_name'] ?? '-') ?></td>
            <td class="label">Due Date</td>
            <td><?= $project['due_date'] ? date('d M Y', strtotime($project['due_date'])) : '-' ?></td>
        </tr>
        <tr>
            <td class="label">PIC / Member</td>
            <td colspan="3">
                <?php if (empty($members)) : ?>
                    -
                <?php else : ?>
                    <?php foreach ($members as $i => $m) : ?><?= esc($m['fullname'] ?? '-') ?> (<?= esc($m['role']) ?>)<?= $i < count($members) - 1 ? ', ' : '' ?><?php endforeach; ?>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <div style="margin: 8px 0;">
        <div class="progress-bg">
            <div class="progress-fill" style="width: <?= (float) $project['progress'] ?>%"></div>
        </div>
        <span style="font-size:9.5px;color:#8392AB;"><?= (float) $project['progress'] ?>% selesai</span>
    </div>

    <?php if (! empty($project['description'])) : ?>
        <div class="section-title">Deskripsi</div>
        <div class="box"><?= nl2br(esc($project['description'])) ?></div>
    <?php endif; ?>

    <?php if (! empty($project['goals'])) : ?>
        <div class="section-title">Goals / Tujuan</div>
        <div class="box"><?= nl2br(esc($project['goals'])) ?></div>
    <?php endif; ?>

    <?php if (! empty($project['problems'])) : ?>
        <div class="section-title">Kendala / Problem</div>
        <div class="box"><?= nl2br(esc($project['problems'])) ?></div>
    <?php endif; ?>
    <?php if (! empty($generalComments)) : ?>
        <div class="section-title">Keterangan / Catatan Project</div>
        <?php foreach ($generalComments as $c) : ?>
            <div class="comment" style="margin-left: 0; margin-bottom: 6px;">
                &raquo; <?= esc($c['comment']) ?> <i>- <?= esc($c['fullname'] ?? 'User') ?>, <?= date('d M Y', strtotime($c['created_at'])) ?></i>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (! empty($milestones)) : ?>
        <div class="section-title">Timeline / Milestone</div>
        <?php foreach ($milestones as $ms) :
            $dotColor = $ms['is_done'] ? '#2DCE89' : (! empty($ms['is_overdue']) ? '#F5365C' : '#8392AB');
        ?>
            <div class="milestone">
                <span class="dot" style="background-color: <?= $dotColor ?>;"></span>
                <b><?= esc($ms['title']) ?></b>
                <span style="color:#8392AB;"> - <?= $ms['target_date'] ? date('d M Y', strtotime($ms['target_date'])) : 'tanpa target' ?><?= $ms['is_done'] ? ' (Selesai)' : '' ?></span>
                <?php if (! empty($ms['description'])) : ?><div style="font-size:9.5px;color:#666;"><?= esc($ms['description']) ?></div><?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="section-title">Checklist Pekerjaan</div>
    <?php if (empty($tasks)) : ?>
        <p style="color:#8392AB;">Belum ada task.</p>
    <?php else : ?>
        <?php foreach ($tasks as $task) : ?>
            <div class="task-block">
                <div class="task-title">[<?= $task['is_done'] ? 'X' : ' ' ?>] <?= esc($task['title']) ?></div>

                <?php foreach ($taskComments[$task['id']] ?? [] as $c) : ?>
                    <div class="comment">
                        <i>&raquo; <?= esc($c['fullname'] ?? 'User') ?>, <?= date('d M Y', strtotime($c['created_at'])) ?>:</i>
                        <div class="comment-body"><?= $c['comment'] ?></div>
                    </div>
                <?php endforeach; ?>

                <?php if (! empty($taskEvidences[$task['id']])) : ?>
                    <div class="attachment-grid" style="margin: 6px 0 6px 18px;">
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
                                    <div style="margin-top: 5px;">
                                        <a href="<?= base_url('uploads/evidence/' . $ev['file_name']) ?>"><?= esc($ev['original_name']) ?></a>
                                        <span style="color:#8392AB;font-size:9px;"> (<?= format_file_size((int) $ev['file_size']) ?>)</span>
                                    </div>
                                    <?php if (! empty($ev['caption'])) : ?>
                                        <div class="attachment-caption"><?= esc($ev['caption']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php foreach ($task['subtasks'] as $sub) : ?>
                    <div class="subtask">
                        <div class="task-title">[<?= $sub['is_done'] ? 'X' : ' ' ?>] <?= esc($sub['title']) ?></div>

                        <?php foreach ($taskComments[$sub['id']] ?? [] as $c) : ?>
                            <div class="comment">
                                <i>&raquo; <?= esc($c['fullname'] ?? 'User') ?>, <?= date('d M Y', strtotime($c['created_at'])) ?>:</i>
                                <div class="comment-body"><?= $c['comment'] ?></div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (! empty($taskEvidences[$sub['id']])) : ?>
                            <div class="attachment-grid" style="margin: 6px 0 6px 12px;">
                                <?php foreach ($taskEvidences[$sub['id']] as $ev) : ?>
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
                                            <div style="margin-top: 5px;">
                                                <a href="<?= base_url('uploads/evidence/' . $ev['file_name']) ?>"><?= esc($ev['original_name']) ?></a>
                                                <span style="color:#8392AB;font-size:9px;"> (<?= format_file_size((int) $ev['file_size']) ?>)</span>
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
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>

</html>