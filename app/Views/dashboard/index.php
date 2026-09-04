<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .kanban-column {
        min-height: 150px;
    }

    .kanban-card {
        cursor: grab;
    }

    .kanban-card.sortable-ghost {
        opacity: .4;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success text-white"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Project</p>
                        <h5 class="font-weight-bolder mb-0"><?= $totalProjects ?></h5>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                            <i class="ni ni-folder-17 text-lg opacity-10"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Overdue</p>
                        <h5 class="font-weight-bolder mb-0"><?= $overdueCount ?></h5>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                            <i class="ni ni-time-alarm text-lg opacity-10"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Selesai</p>
                        <h5 class="font-weight-bolder mb-0"><?= $doneCount ?></h5>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                            <i class="ni ni-check-bold text-lg opacity-10"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-8">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Progress Rata-rata</p>
                        <h5 class="font-weight-bolder mb-0"><?= $avgProgress ?>%</h5>
                    </div>
                    <div class="col-4 text-end">
                        <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                            <i class="ni ni-chart-bar-32 text-lg opacity-10"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Kanban Board</h6>
    <button class="btn btn-sm bg-gradient-primary mb-0" data-bs-toggle="modal" data-bs-target="#quickCreateModal">
        <i class="ni ni-fat-add me-1"></i> Project Baru
    </button>
</div>

<div class="d-flex overflow-auto pb-3" style="gap: 1rem;">
    <?php foreach ($statuses as $status) : ?>
        <div style="min-width: 280px; max-width: 280px;">
            <div class="card">
                <div class="card-header py-2" style="border-top: 3px solid <?= esc($status['color']) ?>;">
                    <h6 class="mb-0 text-sm"><?= esc($status['name']) ?>
                        <span class="badge bg-secondary ms-1"><?= count($board[$status['id']] ?? []) ?></span>
                    </h6>
                </div>
                <div class="card-body kanban-column p-2" data-status-id="<?= $status['id'] ?>">
                    <?php foreach ($board[$status['id']] ?? [] as $project) : ?>
                        <div class="card kanban-card mb-2 shadow-sm" data-project-id="<?= $project['id'] ?>">
                            <div class="card-body p-2">
                                <?php if ($project['is_overdue']) : ?>
                                    <span class="badge bg-gradient-danger mb-1">Overdue</span>
                                <?php endif; ?>
                                <p class="text-xs text-secondary mb-1"><?= esc($project['project_code']) ?></p>
                                <p class="text-sm font-weight-bold mb-1"><?= esc($project['title']) ?></p>
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge me-1" style="background-color: <?= esc($project['priority_color'] ?? '#ccc') ?>"><?= esc($project['priority_name'] ?? '-') ?></span>
                                    <span class="text-xs text-secondary"><?= esc($project['team_name'] ?? '-') ?></span>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-gradient-info" style="width: <?= (float) $project['progress'] ?>%"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="text-xs text-secondary"><?= $project['due_date'] ? date('d M', strtotime($project['due_date'])) : '-' ?></span>
                                    <a href="<?= site_url('tasks/project/' . $project['id']) ?>" class="text-xs text-primary">Checklist &rarr;</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Quick Create Modal -->
<div class="modal fade" id="quickCreateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= site_url('dashboard/quick-create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h6 class="modal-title">Project Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label>Kode Project</label>
                        <input type="text" name="project_code" class="form-control" required>
                    </div>
                    <div class="mb-3"><label>Judul Project</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3"><label>Tim</label>
                        <select name="team_id" class="form-control" required>
                            <?php foreach ($teams as $team) : ?>
                                <option value="<?= $team['id'] ?>"><?= esc($team['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label>Area</label>
                        <select name="area_id" class="form-control" required>
                            <?php foreach ($areas as $area) : ?>
                                <option value="<?= $area['id'] ?>"><?= esc($area['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label>Kategori</label>
                        <select name="category_id" class="form-control" required>
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?= $category['id'] ?>"><?= esc($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label>Prioritas</label>
                        <select name="priority_id" class="form-control" required>
                            <?php foreach ($priorities as $priority) : ?>
                                <option value="<?= $priority['id'] ?>"><?= esc($priority['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label>Due Date</label>
                        <input type="date" name="due_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn bg-gradient-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let csrfName = '<?= csrf_token() ?>';
        let csrfHash = '<?= csrf_hash() ?>';

        document.querySelectorAll('.kanban-column').forEach(function(column) {
            new Sortable(column, {
                group: 'kanban',
                animation: 150,
                onEnd: function(evt) {
                    const projectId = evt.item.getAttribute('data-project-id');
                    const statusId = evt.to.getAttribute('data-status-id');

                    const formData = new FormData();
                    formData.append(csrfName, csrfHash);
                    formData.append('project_id', projectId);
                    formData.append('status_id', statusId);

                    fetch('<?= site_url('dashboard/update-status') ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.csrfHash) csrfHash = data.csrfHash;
                            if (!data.success) {
                                alert(data.message || 'Gagal update status.');
                                location.reload();
                            }
                        })
                        .catch(() => {
                            alert('Terjadi kesalahan koneksi.');
                            location.reload();
                        });
                },
            });
        });
    });
</script>
<?= $this->endSection() ?>