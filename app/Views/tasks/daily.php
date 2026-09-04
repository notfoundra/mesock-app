<?= $this->extend('layouts/main') ?>
<?php // app/Views/tasks/daily.php 
?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Checklist Harian - <?= esc($date) ?></h6>
    <a href="<?= site_url('tasks/daily/templates') ?>" class="btn btn-sm btn-outline-secondary mb-0">Kelola Template</a>
</div>

<form method="get" class="mb-3">
    <input type="date" name="date" value="<?= esc($date) ?>" class="form-control d-inline-block w-auto" onchange="this.form.submit()">
</form>

<div class="card">
    <div class="card-body">
        <?php if (empty($tasks)) : ?>
            <p class="text-secondary text-center py-4">Nggak ada task harian buat tanggal ini. Cek "Kelola Template".</p>
        <?php endif; ?>

        <?php foreach ($tasks as $task) : ?>
            <div class="form-check mb-2">
                <form action="<?= site_url('tasks/daily/' . $task['id'] . '/toggle') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input class="form-check-input" type="checkbox" onchange="this.form.submit()" <?= $task['is_done'] ? 'checked' : '' ?>>
                </form>
                <label class="form-check-label <?= $task['is_done'] ? 'text-decoration-line-through text-secondary' : '' ?>">
                    <?= esc($task['title']) ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>