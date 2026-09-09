<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success text-white"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<form method="get" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="project_id" class="form-control" onchange="this.form.submit()">
            <option value="">-- Semua Project --</option>
            <?php foreach ($projects as $p) : ?>
                <option value="<?= $p['id'] ?>" <?= $filters['project_id'] == $p['id'] ? 'selected' : '' ?>><?= esc($p['project_code'] . ' - ' . $p['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="category_id" class="form-control" onchange="this.form.submit()">
            <option value="">-- Semua Kategori --</option>
            <?php foreach ($categories as $c) : ?>
                <option value="<?= $c['id'] ?>" <?= $filters['category_id'] == $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="source" class="form-control" onchange="this.form.submit()">
            <option value="">-- Semua Sumber --</option>
            <option value="project" <?= $filters['source'] === 'project' ? 'selected' : '' ?>>Task Project</option>
            <option value="daily" <?= $filters['source'] === 'daily' ? 'selected' : '' ?>>Task Harian</option>
        </select>
    </div>
    <div class="col-md-2">
        <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from']) ?>" onchange="this.form.submit()">
    </div>
    <div class="col-md-2">
        <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to']) ?>" onchange="this.form.submit()">
    </div>
    <div class="col-md-1">
        <a href="<?= site_url('evidence') ?>" class="btn btn-outline-secondary w-100 mb-0">Reset</a>
    </div>
</form>

<div class="row">
    <?php foreach ($evidences as $ev) : ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <a href="<?= base_url('uploads/evidence/' . $ev['file_name']) ?>" target="_blank">
                    <?php if (is_image_mime($ev['mime_type'])) : ?>
                        <img src="<?= base_url('uploads/evidence/' . $ev['file_name']) ?>" class="card-img-top" style="height:140px;object-fit:cover;">
                    <?php else : ?>
                        <div class="d-flex flex-column align-items-center justify-content-center bg-light" style="height:140px;">
                            <i class="<?= file_icon_class($ev['mime_type']) ?>" style="font-size:2.5rem;"></i>
                            <span class="text-xs text-secondary mt-1 px-2 text-truncate w-100 text-center">
                                <?= esc($ev['original_name']) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </a>
                <div class="card-body p-2">
                    <span class="badge mb-1" style="background-color: <?= esc($ev['category_color'] ?? '#ccc') ?>"><?= esc($ev['category_name'] ?? '-') ?></span>
                    <p class="text-xs mb-0"><?= esc($ev['caption'] ?: '-') ?></p>
                    <p class="text-xs text-secondary mb-0"><?= format_file_size((int) $ev['file_size']) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($evidences)) : ?>
        <p class="text-secondary text-center py-5">Belum ada bukti pekerjaan yang cocok dengan filter ini.</p>
    <?php endif; ?>
</div>

<?= $pager->links() ?>
<?= $this->endSection() ?>