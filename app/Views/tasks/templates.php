<?= $this->extend('layouts/main') ?>
<?php // app/Views/tasks/templates.php 
?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success text-white"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">Tambah Template Task Harian</h6>
    </div>
    <div class="card-body">
        <form action="<?= site_url('tasks/daily/templates/store') ?>" method="post" class="row g-2">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <select name="team_id" class="form-control" required>
                    <option value="">-- Pilih Tim --</option>
                    <?php foreach ($teams as $team) : ?>
                        <option value="<?= $team['id'] ?>"><?= esc($team['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="title" class="form-control" placeholder="Judul task harian" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="description" class="form-control" placeholder="Deskripsi (opsional)">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn bg-gradient-primary w-100 mb-0">Tambah</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Daftar Template</h6>
    </div>
    <div class="card-body px-0">
        <table class="table align-items-center mb-0">
            <thead>
                <tr>
                    <th class="text-xs text-secondary text-uppercase">Tim</th>
                    <th class="text-xs text-secondary text-uppercase">Judul</th>
                    <th class="text-xs text-secondary text-uppercase">Status</th>
                    <th class="text-xs text-secondary text-uppercase text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($templates as $t) : ?>
                    <tr>
                        <td class="text-sm"><?= esc($t['team_name'] ?? '-') ?></td>
                        <td class="text-sm"><?= esc($t['title']) ?></td>
                        <td class="text-sm">
                            <?= $t['is_active'] ? '<span class="badge bg-gradient-success">Aktif</span>' : '<span class="badge bg-gradient-secondary">Nonaktif</span>' ?>
                        </td>
                        <td class="text-end">
                            <form action="<?= site_url('tasks/daily/templates/' . $t['id'] . '/toggle') ?>" method="post" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-link text-dark px-2 mb-0"><i class="ni ni-curved-next"></i></button>
                            </form>
                            <form action="<?= site_url('tasks/daily/templates/' . $t['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus template?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-link text-danger px-2 mb-0"><i class="ni ni-fat-remove"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>