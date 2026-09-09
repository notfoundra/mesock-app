<?= $this->extend('layouts/main') ?>
<?php // app/Views/tasks/index.php 
?>

<?= $this->section('styles') ?>
<style>
    .project-pick-card {
        transition: transform .15s ease, box-shadow .15s ease;
        border-left: 4px solid transparent;
    }

    .project-pick-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 .75rem 1.5rem rgba(0, 0, 0, .08) !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h6 class="mb-0">Pilih Project</h6>
        <p class="text-sm text-secondary mb-0">Klik project buat lihat & kelola checklist-nya</p>
    </div>
    <a href="<?= site_url('tasks/daily') ?>" class="btn btn-sm bg-gradient-info mb-0">
        <i class="ni ni-calendar-grid-58 me-1"></i> Checklist Harian Tim
    </a>
</div>

<div class="row">
    <?php foreach ($projects as $project) : ?>
        <div class="col-md-4 mb-4">
            <a href="<?= site_url('tasks/project/' . $project['id']) ?>" class="text-decoration-none">
                <div class="card h-100 project-pick-card" style="border-left-color: <?= esc($project['status_color'] ?? '#ccc') ?>;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon icon-shape icon-sm bg-gradient-dark shadow text-center border-radius-md">
                                <i class="ni ni-folder-17 text-white opacity-10"></i>
                            </div>
                            <span class="badge" style="background-color: <?= esc($project['status_color'] ?? '#ccc') ?>">
                                <?= esc($project['status_name'] ?? '-') ?>
                            </span>
                        </div>

                        <h6 class="text-dark mb-1"><?= esc($project['title']) ?></h6>
                        <p class="text-xs text-secondary mb-3">
                            <i class="ni ni-single-02 me-1"></i><?= esc($project['team_name'] ?? '-') ?>
                        </p>

                        <div class="progress mb-1" style="height: 5px;">
                            <div class="progress-bar bg-gradient-info" style="width: <?= (float) $project['progress'] ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-xs text-secondary"><?= (float) $project['progress'] ?>% selesai</span>
                            <span class="text-xs text-secondary">
                                <i class="ni ni-calendar-grid-58 me-1"></i>
                                <?= $project['due_date'] ? date('d M Y', strtotime($project['due_date'])) : 'Tanpa deadline' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>

    <?php if (empty($projects)) : ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ni ni-folder-17 text-secondary opacity-6 mb-3 d-block" style="font-size: 2.5rem;"></i>
                    <h6 class="text-secondary mb-1">Belum ada project</h6>
                    <p class="text-sm text-secondary mb-3">Bikin project dulu biar checklist-nya bisa mulai dipakai.</p>
                    <a href="<?= site_url('projects/create') ?>" class="btn btn-sm bg-gradient-primary mb-0">
                        <i class="ni ni-fat-add me-1"></i> Project Baru
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>