<?= $this->extend('layouts/main') ?>
<?php // app/Views/tasks/index.php 
?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Pilih Project</h6>
    <a href="<?= site_url('tasks/daily') ?>" class="btn btn-sm bg-gradient-info mb-0">
        <i class="ni ni-calendar-grid-58 me-1"></i> Checklist Harian Tim
    </a>
</div>

<div class="row">
    <?php foreach ($projects as $project) : ?>
        <div class="col-md-4 mb-4">
            <a href="<?= site_url('tasks/project/' . $project['id']) ?>" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="badge mb-2" style="background-color: <?= esc($project['status_color'] ?? '#ccc') ?>">
                            <?= esc($project['status_name'] ?? '-') ?>
                        </span>
                        <h6 class="text-dark"><?= esc($project['title']) ?></h6>
                        <p class="text-sm text-secondary mb-0"><?= esc($project['team_name'] ?? '-') ?></p>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
    <?php if (empty($projects)) : ?>
        <p class="text-secondary">Belum ada project. Bikin dulu di menu "Semua Project".</p>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>