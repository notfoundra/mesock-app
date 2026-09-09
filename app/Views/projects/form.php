<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $errors = session()->getFlashdata('errors'); ?>
<?php if (! empty($errors)) : ?>
    <div class="alert alert-danger text-white">
        <ul class="mb-0 ps-3">
            <?php foreach ((array) $errors as $error) : ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><?= esc($title) ?></h6>
    </div>
    <div class="card-body">
        <form action="<?= $isEdit ? site_url('projects/' . $row['id'] . '/update') : site_url('projects/store') ?>" method="post" class="row g-3">
            <?= csrf_field() ?>

            <div class="col-md-4">
                <label>Kode Project</label>
                <input type="text" name="project_code" class="form-control" value="<?= old('project_code') ?? esc($row['project_code'] ?? '') ?>" required>
            </div>
            <div class="col-md-8">
                <label>Judul Project</label>
                <input type="text" name="title" class="form-control" value="<?= old('title') ?? esc($row['title'] ?? '') ?>" required>
            </div>

            <div class="col-md-12">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"><?= old('description') ?? esc($row['description'] ?? '') ?></textarea>
            </div>

            <div class="col-md-3">
                <label>Tim</label>
                <select name="team_id" class="form-control" required>
                    <?php foreach ($teams as $team) : ?>
                        <option value="<?= $team['id'] ?>" <?= ($row['team_id'] ?? null) == $team['id'] ? 'selected' : '' ?>><?= esc($team['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Area</label>
                <select name="area_id" class="form-control" required>
                    <?php foreach ($areas as $area) : ?>
                        <option value="<?= $area['id'] ?>" <?= ($row['area_id'] ?? null) == $area['id'] ? 'selected' : '' ?>><?= esc($area['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Kategori</label>
                <select name="category_id" class="form-control" required>
                    <?php foreach ($categories as $category) : ?>
                        <option value="<?= $category['id'] ?>" <?= ($row['category_id'] ?? null) == $category['id'] ? 'selected' : '' ?>><?= esc($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Prioritas</label>
                <select name="priority_id" class="form-control" required>
                    <?php foreach ($priorities as $priority) : ?>
                        <option value="<?= $priority['id'] ?>" <?= ($row['priority_id'] ?? null) == $priority['id'] ? 'selected' : '' ?>><?= esc($priority['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label>Status</label>
                <select name="status_id" class="form-control" required>
                    <?php foreach ($statuses as $status) : ?>
                        <option value="<?= $status['id'] ?>" <?= ($row['status_id'] ?? null) == $status['id'] ? 'selected' : '' ?>><?= esc($status['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="<?= esc($row['start_date'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label>Due Date</label>
                <input type="date" name="due_date" class="form-control" value="<?= esc($row['due_date'] ?? '') ?>">
            </div>

            <div class="col-md-12">
                <button type="submit" class="btn bg-gradient-primary">Simpan</button>
                <a href="<?= site_url('projects') ?>" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>