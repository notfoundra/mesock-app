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

        .box {
            border: 1px solid #e9ecef;
            padding: 8px 10px;
            border-radius: 4px;
            font-size: 10.5px;
            margin-bottom: 8px;
        }

        .box img {
            max-width: 150px;
        }

        .box table {
            border-collapse: collapse;
            width: 100%;
            margin: 4px 0;
        }

        .box table td,
        .box table th {
            border: 1px solid #ccc;
            padding: 3px 5px;
            font-size: 9px;
        }

        .attachment-grid {
            margin-top: 8px;
        }

        .attachment-card {
            display: inline-block;
            width: 300px;
            vertical-align: top;
            margin: 0 14px 18px 0;
        }

        .attachment-card img {
            width: 300px;
            height: 175px;
            object-fit: cover;
            border: 1px solid #dfe3e8;
            border-radius: 5px;
        }

        .cat-badge {
            display: inline-block;
            font-size: 10px;
            color: #fff;
            padding: 2px 7px;
            border-radius: 3px;
            margin-top: 5px;
            letter-spacing: .3px;
        }

        .attachment-caption {
            font-size: 11px;
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
    </style>
</head>

<body>

    <div class="header">
        <div class="brand">ProjectTrack &middot; Notulensi Meeting</div>
        <div class="doctitle"><?= esc($meeting['title']) ?></div>
    </div>

    <div class="footer">Dicetak <?= esc($generatedAt) ?> &middot; ProjectTrack</div>

    <table class="info">
        <tr>
            <td class="label">Tanggal</td>
            <td><?= date('d M Y', strtotime($meeting['meeting_date'])) ?></td>
            <td class="label">Dibuat oleh</td>
            <td><?= esc($meeting['creator_name'] ?? '-') ?></td>
        </tr>
        <tr>
            <td class="label">Project</td>
            <td colspan="3"><?= $meeting['project_title'] ? esc($meeting['project_code'] . ' - ' . $meeting['project_title']) : 'Internal / Umum (tidak terkait project spesifik)' ?></td>
        </tr>
    </table>

    <?php if (! empty($meeting['problem'])) : ?>
        <div class="section-title">Masalah yang Dibahas</div>
        <div class="box"><?= $meeting['problem'] ?></div>
    <?php endif; ?>

    <?php if (! empty($meeting['expected_outcome'])) : ?>
        <div class="section-title">Hasil yang Diharapkan</div>
        <div class="box"><?= $meeting['expected_outcome'] ?></div>
    <?php endif; ?>

    <?php if (! empty($meeting['notes'])) : ?>
        <div class="section-title">Notulensi Lengkap</div>
        <div class="box"><?= $meeting['notes'] ?></div>
    <?php endif; ?>

    <?php if (! empty($evidences)) : ?>
        <div class="section-title">Lampiran</div>
        <div class="attachment-grid">
            <?php foreach ($evidences as $ev) : ?>
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
</body>

</html>