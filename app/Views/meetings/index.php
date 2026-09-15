<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success text-white"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Notulensi Meeting</h6>
    <a href="<?= site_url('meetings/create') ?>" class="btn btn-sm bg-gradient-primary mb-0">
        <i class="ni ni-fat-add me-1"></i> Notulensi Baru
    </a>
</div>

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
        <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from']) ?>" onchange="this.form.submit()">
    </div>
    <div class="col-md-2">
        <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to']) ?>" onchange="this.form.submit()">
    </div>
    <div class="col-md-3">
        <input type="text" name="keyword" class="form-control" placeholder="Cari judul meeting..." value="<?= esc($filters['keyword']) ?>">
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-outline-secondary w-100 mb-0">Cari</button>
    </div>
</form>

<div class="row">
    <?php foreach ($meetings as $m) : ?>
        <div class="col-md-4 mb-4">
            <a href="<?= site_url('meetings/' . $m['id']) ?>" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-xs text-secondary mb-1"><?= date('d M Y', strtotime($m['meeting_date'])) ?></p>
                        <h6 class="text-dark mb-1"><?= esc($m['title']) ?></h6>
                        <p class="text-xs text-secondary mb-0">
                            <?php if ($m['project_title']) : ?>
                                <i class="ni ni-folder-17 me-1"></i><?= esc($m['project_code'] . ' - ' . $m['project_title']) ?>
                            <?php else : ?>
                                <i class="ni ni-single-02 me-1"></i>Internal / Umum
                            <?php endif; ?>
                        </p>
                        <p class="text-xs text-secondary mb-0 mt-1">Dibuat oleh <?= esc($m['creator_name'] ?? '-') ?></p>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
    <?php if (empty($meetings)) : ?>
        <div class="col-12">
            <p class="text-secondary text-center py-5">Belum ada notulensi. Klik "Notulensi Baru" buat mulai catet meeting.</p>
        </div>
    <?php endif; ?>
</div>

<?= $pager->links() ?>
<?= $this->endSection() ?>