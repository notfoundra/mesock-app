<?= $this->extend('layouts/main') ?>
<?php
// app/Views/tasks/daily.php
$hariIndo  = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$bulanIndo = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
$ts        = strtotime($date);
$tanggalLabel = $hariIndo[date('l', $ts)] . ', ' . (int) date('d', $ts) . ' ' . $bulanIndo[(int) date('n', $ts)] . ' ' . date('Y', $ts);
$doneCount = count(array_filter($tasks, static fn($t) => $t['is_done']));
$totalCount = count($tasks);
?>

<?= $this->section('styles') ?>
<style>
    .daily-task-row {
        border-bottom: 1px solid #f0f2f5;
        transition: background-color .15s ease;
    }

    .daily-task-row:last-child {
        border-bottom: none;
    }

    .daily-task-row:hover {
        background-color: #f8f9fa;
    }

    .daily-task-row.is-done {
        opacity: .65;
    }

    .daily-check {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="<?= site_url('tasks/daily/history') ?>" class="btn btn-sm btn-outline-primary mb-0 me-1">
            <i class="ni ni-collection me-1"></i> History
        </a>
        <a href="<?= site_url('tasks/daily/templates') ?>" class="btn btn-sm btn-outline-secondary mb-0">
            <i class="ni ni-settings-gear-65 me-1"></i> Kelola Template
        </a>
    </div>
</div>
<form method="get" class="d-flex align-items-center gap-2 mb-3">
    <input type="date" name="date" value="<?= esc($date) ?>" class="form-control w-auto" onchange="this.form.submit()">
    <?php if ($isSuperTeam) : ?>
        <select name="team_id" class="form-control w-auto" onchange="this.form.submit()">
            <option value="">-- Semua Tim --</option>
            <?php foreach ($teams as $team) : ?>
                <option value="<?= $team['id'] ?>" <?= $selectedTeamId == $team['id'] ? 'selected' : '' ?>><?= esc($team['name']) ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
    <a href="<?= site_url('tasks/daily') ?>" class="btn btn-sm btn-outline-primary mb-0">Hari Ini</a>
    <?php if ($totalCount > 0) : ?>
        <span class="text-sm text-secondary ms-auto"><?= $doneCount ?> / <?= $totalCount ?> selesai</span>
    <?php endif; ?>
</form>

<div class="card">
    <?php if (empty($tasks)) : ?>
        <div class="card-body text-center py-5">
            <i class="ni ni-calendar-grid-58 text-secondary opacity-6 mb-3 d-block" style="font-size: 2.5rem;"></i>
            <h6 class="text-secondary mb-1">Belum ada checklist buat tanggal ini</h6>
            <p class="text-sm text-secondary mb-3">Cek "Kelola Template" buat nambahin task harian tim kamu.</p>
            <a href="<?= site_url('tasks/daily/templates') ?>" class="btn btn-sm bg-gradient-primary mb-0">Kelola Template</a>
        </div>
    <?php else : ?>
        <?php foreach ($tasks as $task) : ?>
            <div class="d-flex align-items-center justify-content-between p-3 daily-task-row <?= $task['is_done'] ? 'is-done' : '' ?>">
                <div class="d-flex align-items-center">
                    <form action="<?= site_url('tasks/daily/' . $task['id'] . '/toggle') ?>" method="post" class="d-inline me-3">
                        <?= csrf_field() ?>
                        <input class="form-check-input daily-check" type="checkbox" onchange="this.form.submit()" <?= $task['is_done'] ? 'checked' : '' ?>>
                    </form>
                    <div>
                        <p class="mb-0 <?= $task['is_done'] ? 'text-decoration-line-through text-secondary' : 'text-dark' ?>">
                            <?php if ($isSuperTeam) : ?>
                                <span class="badge bg-gradient-secondary me-1"><?= esc($task['team_name'] ?? '-') ?></span>
                            <?php endif; ?>
                            <?= esc($task['title']) ?>
                        </p>
                        <?php if (! empty($task['description'])) : ?>
                            <p class="text-xs text-secondary mb-0"><?= esc($task['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="<?= site_url('tasks/daily/' . $task['id'] . '/detail') ?>" class="text-xs text-primary text-nowrap ms-2">
                    <i class="ni ni-single-copy-04"></i> Detail
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>