<?= $this->extend('layouts/main') ?>
<?php // app/Views/tasks/project.php 
?>
<?= $this->section('content') ?>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">Tambah Task</h6>
    </div>
    <div class="card-body">
        <form action="<?= site_url('tasks/project/' . $project['id'] . '/store') ?>" method="post" class="row g-2">
            <?= csrf_field() ?>
            <div class="col-md-4">
                <input type="text" name="title" class="form-control" placeholder="Judul task" required>
            </div>
            <div class="col-md-3">
                <select name="parent_id" class="form-control">
                    <option value="">-- Task utama --</option>
                    <?php foreach ($tasks as $t) : ?>
                        <option value="<?= $t['id'] ?>">Subtask dari: <?= esc($t['title']) ?></option>
                    <?php endforeach; ?>
                </select>
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
        <h6 class="mb-0">Checklist - <?= esc($project['title']) ?></h6>
    </div>
    <div class="card-body">
        <?php if (empty($tasks)) : ?>
            <p class="text-secondary text-center py-4">Belum ada task.</p>
        <?php endif; ?>

        <?php foreach ($tasks as $task) : ?>
            <div class="form-check mb-2">
                <form action="<?= site_url('tasks/' . $task['id'] . '/toggle') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input class="form-check-input" type="checkbox" onchange="this.form.submit()" <?= $task['is_done'] ? 'checked' : '' ?>>
                </form>
                <label class="form-check-label <?= $task['is_done'] ? 'text-decoration-line-through text-secondary' : '' ?>">
                    <?= esc($task['title']) ?>
                </label>
                <form action="<?= site_url('tasks/' . $task['id'] . '/delete') ?>" method="post" class="d-inline float-end" onsubmit="return confirm('Hapus task ini?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-link text-danger btn-sm py-0"><i class="ni ni-fat-remove"></i></button>
                </form>
            </div>

            <?php foreach ($task['subtasks'] as $sub) : ?>
                <div class="form-check mb-2 ms-4">
                    <form action="<?= site_url('tasks/' . $sub['id'] . '/toggle') ?>" method="post" class="d-inline">
                        <?= csrf_field() ?>
                        <input class="form-check-input" type="checkbox" onchange="this.form.submit()" <?= $sub['is_done'] ? 'checked' : '' ?>>
                    </form>
                    <label class="form-check-label <?= $sub['is_done'] ? 'text-decoration-line-through text-secondary' : '' ?>">
                        <?= esc($sub['title']) ?>
                    </label>
                    <form action="<?= site_url('tasks/' . $sub['id'] . '/delete') ?>" method="post" class="d-inline float-end" onsubmit="return confirm('Hapus subtask ini?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-link text-danger btn-sm py-0"><i class="ni ni-fat-remove"></i></button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>