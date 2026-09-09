<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .timeline {
        position: relative;
        padding-left: 2rem;
        margin-top: .5rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: .6rem;
        top: .2rem;
        bottom: .2rem;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.75rem;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-dot {
        position: absolute;
        left: -2rem;
        top: .2rem;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px currentColor;
    }

    .timeline-dot.done {
        background: #2DCE89;
        color: #2DCE89;
    }

    .timeline-dot.pending {
        background: #8392AB;
        color: #8392AB;
    }

    .timeline-dot.overdue {
        background: #F5365C;
        color: #F5365C;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<a href="<?= site_url('projects/' . $project['id']) ?>" class="text-sm text-secondary mb-3 d-inline-block">&larr; Kembali ke Detail Project</a>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <p class="text-xs text-secondary mb-0"><?= esc($project['project_code']) ?></p>
        <h5 class="mb-0">Timeline - <?= esc($project['title']) ?></h5>
    </div>
    <span class="badge" style="background-color: <?= esc($project['status_color'] ?? '#ccc') ?>"><?= esc($project['status_name'] ?? '-') ?></span>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Milestone</h6>
            </div>
            <div class="card-body">
                <?php if (empty($milestones)) : ?>
                    <p class="text-secondary text-sm text-center py-4">Belum ada milestone. Tambahin lewat halaman detail project.</p>
                <?php else : ?>
                    <div class="timeline">
                        <?php foreach ($milestones as $ms) :
                            $dotClass = $ms['is_done'] ? 'done' : ($ms['is_overdue'] ? 'overdue' : 'pending');
                        ?>
                            <div class="timeline-item">
                                <span class="timeline-dot <?= $dotClass ?>"></span>
                                <p class="text-sm font-weight-bold mb-0 <?= $ms['is_done'] ? 'text-decoration-line-through text-secondary' : '' ?>">
                                    <?= esc($ms['title']) ?>
                                </p>
                                <?php if (! empty($ms['description'])) : ?>
                                    <p class="text-xs text-secondary mb-1"><?= esc($ms['description']) ?></p>
                                <?php endif; ?>
                                <span class="text-xs <?= $ms['is_overdue'] ? 'text-danger' : 'text-secondary' ?>">
                                    <?= $ms['target_date'] ? date('d M Y', strtotime($ms['target_date'])) : 'Tanpa target tanggal' ?>
                                    <?= $ms['is_done'] ? ' &middot; Selesai' : ($ms['is_overdue'] ? ' &middot; Overdue' : '') ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Aktivitas Terbaru</h6>
            </div>
            <div class="card-body">
                <?php foreach (array_slice($activities, 0, 15) as $a) : ?>
                    <div class="mb-3">
                        <p class="text-sm mb-0"><?= esc($a['activity']) ?></p>
                        <?php if ($a['old_value'] !== null || $a['new_value'] !== null) : ?>
                            <p class="text-xs text-secondary mb-0">
                                <?= esc($a['old_value'] ?? '-') ?> &rarr; <?= esc($a['new_value'] ?? '-') ?>
                            </p>
                        <?php endif; ?>
                        <p class="text-xs text-secondary mb-0">
                            <?= esc($a['fullname'] ?? 'User') ?> &middot; <?= date('d M Y H:i', strtotime($a['created_at'])) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($activities)) : ?>
                    <p class="text-secondary text-sm">Belum ada aktivitas.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>