<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">History Checklist Harian</h6>
    <a href="<?= site_url('tasks/daily') ?>" class="btn btn-sm btn-outline-secondary mb-0">&larr; Kembali ke Hari Ini</a>
</div>

<form method="get" class="row g-2 mb-3">
    <?php if ($isSuperTeam) : ?>
        <div class="col-md-2">
            <select name="team_id" class="form-control" onchange="this.form.submit()">
                <option value="">-- Semua Tim --</option>
                <?php foreach ($teams as $team) : ?>
                    <option value="<?= $team['id'] ?>" <?= $filters['team_id'] == $team['id'] ? 'selected' : '' ?>><?= esc($team['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
    <div class="col-md-2">
        <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from']) ?>" onchange="this.form.submit()">
    </div>
    <div class="col-md-2">
        <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to']) ?>" onchange="this.form.submit()">
    </div>
    <div class="col-md-2">
        <select name="is_done" class="form-control" onchange="this.form.submit()">
            <option value="">-- Semua Status --</option>
            <option value="1" <?= $filters['is_done'] === '1' ? 'selected' : '' ?>>Selesai</option>
            <option value="0" <?= $filters['is_done'] === '0' ? 'selected' : '' ?>>Belum Selesai</option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="text" name="keyword" class="form-control" placeholder="Cari judul task..." value="<?= esc($filters['keyword']) ?>">
    </div>
    <div class="col-md-1">
        <button type="submit" class="btn btn-outline-secondary w-100 mb-0">Cari</button>
    </div>
</form>

<div class="card">
    <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th class="text-xs text-secondary text-uppercase">Tanggal</th>
                        <?php if ($isSuperTeam) : ?><th class="text-xs text-secondary text-uppercase">Tim</th><?php endif; ?>
                        <th class="text-xs text-secondary text-uppercase">Task</th>
                        <th class="text-xs text-secondary text-uppercase">Status</th>
                        <th class="text-xs text-secondary text-uppercase">Dikerjakan Oleh</th>
                        <th class="text-xs text-secondary text-uppercase">Temuan</th>
                        <th class="text-xs text-secondary text-uppercase text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)) : ?>
                        <tr>
                            <td colspan="99" class="text-center text-secondary py-4">Nggak ada data yang cocok.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($history as $h) : ?>
                        <tr>
                            <td class="text-sm text-nowrap"><?= date('d M Y', strtotime($h['task_date'])) ?></td>
                            <?php if ($isSuperTeam) : ?><td class="text-sm"><?= esc($h['team_name'] ?? '-') ?></td><?php endif; ?>
                            <td class="text-sm"><?= esc($h['title']) ?></td>
                            <td class="text-sm">
                                <?= $h['is_done'] ? '<span class="badge bg-gradient-success">Selesai</span>' : '<span class="badge bg-gradient-secondary">Belum</span>' ?>
                            </td>
                            <td class="text-sm"><?= esc($h['done_by_name'] ?? '-') ?></td>
                            <td class="text-sm">
                                <?php $count = $commentCounts[$h['id']] ?? 0; ?>
                                <?php if ($count > 0) : ?>
                                    <span class="badge bg-gradient-warning"><?= $count ?> catatan</span>
                                <?php else : ?>
                                    <span class="text-xs text-secondary">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= site_url('tasks/daily/' . $h['id'] . '/detail') ?>" class="text-xs text-primary">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $pager->links() ?>
<?= $this->endSection() ?>