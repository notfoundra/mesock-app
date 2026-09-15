<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success text-white"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger text-white"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <p class="text-xs text-secondary mb-0"><?= date('d M Y', strtotime($meeting['meeting_date'])) ?></p>
        <h5 class="mb-0"><?= esc($meeting['title']) ?></h5>
        <p class="text-xs text-secondary mb-0">
            <?php if ($meeting['project_title']) : ?>
                Terkait project: <a href="<?= site_url('projects/' . $meeting['project_id']) ?>"><?= esc($meeting['project_code'] . ' - ' . $meeting['project_title']) ?></a>
            <?php else : ?>
                Meeting internal / umum
            <?php endif; ?>
        </p>
    </div>
    <div>
        <a href="<?= site_url('meetings/' . $meeting['id'] . '/export-pdf') ?>" target="_blank" class="btn btn-sm btn-outline-dark mb-0">
            <i class="ni ni-single-copy-04 me-1"></i> Export PDF
        </a>
        <a href="<?= site_url('meetings/' . $meeting['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary mb-0">Edit</a>
        <form action="<?= site_url('meetings/' . $meeting['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus notulensi ini?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-outline-danger mb-0">Hapus</button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <?php if (! empty($meeting['problem'])) : ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Masalah yang Dibahas</h6>
                </div>
                <div class="card-body comment-body"><?= $meeting['problem'] ?></div>
            </div>
        <?php endif; ?>

        <?php if (! empty($meeting['expected_outcome'])) : ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Hasil yang Diharapkan</h6>
                </div>
                <div class="card-body comment-body"><?= $meeting['expected_outcome'] ?></div>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Notulensi Lengkap</h6>
            </div>
            <div class="card-body comment-body">
                <?= ! empty($meeting['notes']) ? $meeting['notes'] : '<p class="text-secondary">Belum ada notulensi.</p>' ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Lampiran</h6>
            </div>
            <div class="card-body">
                <form action="<?= site_url('meetings/' . $meeting['id'] . '/attachment') ?>" method="post" enctype="multipart/form-data" class="mb-3">
                    <?= csrf_field() ?>
                    <input type="file" name="attachment" class="form-control mb-2" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.txt" required>
                    <select name="category_id" class="form-control mb-2" required>
                        <?php foreach ($categories as $cat) : ?>
                            <option value="<?= $cat['id'] ?>"><?= esc($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="caption" class="form-control mb-2" placeholder="Caption (opsional)">
                    <button type="submit" class="btn btn-sm bg-gradient-primary w-100 mb-0">Upload</button>
                </form>

                <?php foreach ($evidences as $ev) : ?>
                    <div class="d-flex align-items-center mb-2">
                        <a href="<?= base_url('uploads/evidence/' . $ev['file_name']) ?>" target="_blank" class="d-flex align-items-center text-decoration-none flex-grow-1">
                            <?php if (is_image_mime($ev['mime_type'])) : ?>
                                <img src="<?= base_url('uploads/evidence/' . $ev['file_name']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:4px;" class="me-2">
                            <?php else : ?>
                                <i class="<?= file_icon_class($ev['mime_type']) ?> me-2" style="font-size:1.5rem;"></i>
                            <?php endif; ?>
                            <span class="text-xs text-dark text-truncate"><?= esc($ev['original_name']) ?></span>
                        </a>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($evidences)) : ?>
                    <p class="text-secondary text-sm">Belum ada lampiran.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>