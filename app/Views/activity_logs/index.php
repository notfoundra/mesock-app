<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h6 class="mb-3">Audit Sistem</h6>

<form method="get" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="project_id" class="form-control" onchange="this.form.submit()">
            <option value="">-- Semua Project --</option>
            <?php foreach ($projects as $p) : ?>
                <option value="<?= $p['id'] ?>" <?= $filters['project_id'] == $p['id'] ? 'selected' : '' ?>><?= esc($p['project_code'] . ' - ' . $p['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <select name="user_id" class="form-control" onchange="this.form.submit()">
            <option value="">-- Semua User --</option>
            <?php foreach ($users as $u) : ?>
                <option value="<?= $u['user_id'] ?>" <?= $filters['user_id'] == $u['user_id'] ? 'selected' : '' ?>><?= esc($u['fullname']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from']) ?>" onchange="this.form.submit()">
    </div>
    <div class="col-md-2">
        <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to']) ?>" onchange="this.form.submit()">
    </div>
    <div class="col-md-2">
        <a href="<?= site_url('activity-logs') ?>" class="btn btn-outline-secondary w-100 mb-0">Reset</a>
    </div>
</form>

<div class="card">
    <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th class="text-xs text-secondary text-uppercase">Waktu</th>
                        <th class="text-xs text-secondary text-uppercase">User</th>
                        <th class="text-xs text-secondary text-uppercase">Project</th>
                        <th class="text-xs text-secondary text-uppercase">Aktivitas</th>
                        <th class="text-xs text-secondary text-uppercase">Perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)) : ?>
                        <tr>
                            <td colspan="99" class="text-center text-secondary py-4">Belum ada aktivitas tercatat.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($logs as $log) : ?>
                        <tr>
                            <td class="text-sm text-nowrap"><?= date('d M Y H:i', strtotime($log['created_at'])) ?></td>
                            <td class="text-sm"><?= esc($log['fullname'] ?? ('User #' . $log['user_id'])) ?></td>
                            <td class="text-sm">
                                <?php if ($log['project_title']) : ?>
                                    <a href="<?= site_url('projects/' . $log['project_id']) ?>"><?= esc($log['project_code'] . ' - ' . $log['project_title']) ?></a>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="text-sm"><?= esc($log['activity']) ?></td>
                            <td class="text-sm">
                                <?php if ($log['old_value'] !== null || $log['new_value'] !== null) : ?>
                                    <span class="text-secondary text-decoration-line-through"><?= esc($log['old_value'] ?? '-') ?></span>
                                    &rarr;
                                    <span class="text-dark"><?= esc($log['new_value'] ?? '-') ?></span>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
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