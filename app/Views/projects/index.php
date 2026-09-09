<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success text-white"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Semua Project</h6>
    <a href="<?= site_url('projects/create') ?>" class="btn btn-sm bg-gradient-primary mb-0">
        <i class="ni ni-fat-add me-1"></i> Project Baru
    </a>
</div>

<form method="get" class="row g-2 mb-3">
    <div class="col-md-3">
        <input type="text" name="q" class="form-control" placeholder="Cari kode/judul..." value="<?= esc($filters['q']) ?>">
    </div>
    <div class="col-md-3">
        <select name="team_id" class="form-control" onchange="this.form.submit()">
            <option value="">-- Semua Tim --</option>
            <?php foreach ($teams as $team) : ?>
                <option value="<?= $team['id'] ?>" <?= $filters['team_id'] == $team['id'] ? 'selected' : '' ?>><?= esc($team['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="status_id" class="form-control" onchange="this.form.submit()">
            <option value="">-- Semua Status --</option>
            <?php foreach ($statuses as $status) : ?>
                <option value="<?= $status['id'] ?>" <?= $filters['status_id'] == $status['id'] ? 'selected' : '' ?>><?= esc($status['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-outline-secondary w-100 mb-0">Filter</button>
    </div>
</form>

<div class="card">
    <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th class="text-xs text-secondary text-uppercase">Kode</th>
                        <th class="text-xs text-secondary text-uppercase">Judul</th>
                        <th class="text-xs text-secondary text-uppercase">Tim</th>
                        <th class="text-xs text-secondary text-uppercase">Prioritas</th>
                        <th class="text-xs text-secondary text-uppercase">Status</th>
                        <th class="text-xs text-secondary text-uppercase">Progress</th>
                        <th class="text-xs text-secondary text-uppercase">Due Date</th>
                        <th class="text-xs text-secondary text-uppercase text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projects)) : ?>
                        <tr>
                            <td colspan="99" class="text-center text-secondary py-4">Belum ada project.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($projects as $p) : ?>
                        <tr>
                            <td class="text-sm"><a href="<?= site_url('projects/' . $p['id']) ?>"><?= esc($p['project_code']) ?></a></td>
                            <td class="text-sm">
                                <?= esc($p['title']) ?>
                                <?php if ($p['is_overdue']) : ?><span class="badge bg-gradient-danger ms-1">Overdue</span><?php endif; ?>
                            </td>
                            <td class="text-sm"><?= esc($p['team_name'] ?? '-') ?></td>
                            <td class="text-sm">
                                <span class="badge" style="background-color: <?= esc($p['priority_color'] ?? '#ccc') ?>"><?= esc($p['priority_name'] ?? '-') ?></span>
                            </td>
                            <td class="text-sm">
                                <span class="badge" style="background-color: <?= esc($p['status_color'] ?? '#ccc') ?>"><?= esc($p['status_name'] ?? '-') ?></span>
                            </td>
                            <td class="text-sm" style="min-width:120px;">
                                <div class="progress" style="height:6px;">
                                    <div class="progress-bar bg-gradient-info" style="width: <?= (float) $p['progress'] ?>%"></div>
                                </div>
                                <span class="text-xs"><?= (float) $p['progress'] ?>%</span>
                            </td>
                            <td class="text-sm"><?= $p['due_date'] ? date('d M Y', strtotime($p['due_date'])) : '-' ?></td>
                            <td class="text-end">
                                <a href="<?= site_url('projects/' . $p['id']) ?>" class="btn btn-link text-info px-2 mb-0"><i class="ni ni-single-copy-04"></i></a>
                                <a href="<?= site_url('projects/' . $p['id'] . '/edit') ?>" class="btn btn-link text-dark px-2 mb-0"><i class="ni ni-ruler-pencil"></i></a>
                                <form action="<?= site_url('projects/' . $p['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus project ini?');">
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
</div>
<?= $this->endSection() ?>