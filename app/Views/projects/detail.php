<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success text-white"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger text-white"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <p class="text-xs text-secondary mb-0"><?= esc($project['project_code']) ?></p>
        <h5 class="mb-0"><?= esc($project['title']) ?>
            <?php if ($project['is_overdue']) : ?><span class="badge bg-gradient-danger ms-1">Overdue</span><?php endif; ?>
        </h5>
    </div>
    <div>
        <a href="<?= site_url('tasks/project/' . $project['id']) ?>" class="btn btn-sm bg-gradient-info mb-0">Checklist</a>
        <a href="<?= site_url('projects/' . $project['id'] . '/timeline') ?>" class="btn btn-sm btn-outline-primary mb-0">Timeline</a>
        <a href="<?= site_url('projects/' . $project['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary mb-0">Edit</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Info Project</h6>
            </div>
            <div class="card-body">
                <p class="text-sm"><?= nl2br(esc($project['description'] ?: '-')) ?></p>
                <div class="row text-sm">
                    <div class="col-6 mb-2"><b>Tim:</b> <?= esc($project['team_name'] ?? '-') ?></div>
                    <div class="col-6 mb-2"><b>Area:</b> <?= esc($project['area_name'] ?? '-') ?></div>
                    <div class="col-6 mb-2"><b>Kategori:</b> <?= esc($project['category_name'] ?? '-') ?></div>
                    <div class="col-6 mb-2">
                        <b>Prioritas:</b>
                        <span class="badge" style="background-color: <?= esc($project['priority_color'] ?? '#ccc') ?>"><?= esc($project['priority_name'] ?? '-') ?></span>
                    </div>
                    <div class="col-6 mb-2">
                        <b>Status:</b>
                        <span class="badge" style="background-color: <?= esc($project['status_color'] ?? '#ccc') ?>"><?= esc($project['status_name'] ?? '-') ?></span>
                    </div>
                    <div class="col-6 mb-2"><b>Due Date:</b> <?= $project['due_date'] ? date('d M Y', strtotime($project['due_date'])) : '-' ?></div>
                </div>
                <div class="progress mt-2" style="height:6px;">
                    <div class="progress-bar bg-gradient-info" style="width: <?= (float) $project['progress'] ?>%"></div>
                </div>
                <span class="text-xs"><?= (float) $project['progress'] ?>% selesai</span>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Bukti Pekerjaan</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($evidences as $ev) : ?>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100">
                                <a href="<?= base_url('uploads/evidence/' . $ev['file_name']) ?>" target="_blank">
                                    <?php if (is_image_mime($ev['mime_type'])) : ?>
                                        <img src="<?= base_url('uploads/evidence/' . $ev['file_name']) ?>" class="card-img-top" style="height:140px;object-fit:cover;">
                                    <?php else : ?>
                                        <div class="d-flex flex-column align-items-center justify-content-center bg-light" style="height:140px;">
                                            <i class="<?= file_icon_class($ev['mime_type']) ?>" style="font-size:2.5rem;"></i>
                                            <span class="text-xs text-secondary mt-1 px-2 text-truncate w-100 text-center">
                                                <?= esc($ev['original_name']) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                <div class="card-body p-2">
                                    <span class="badge mb-1" style="background-color: <?= esc($ev['category_color'] ?? '#ccc') ?>"><?= esc($ev['category_name'] ?? '-') ?></span>
                                    <p class="text-xs mb-0"><?= esc($ev['caption'] ?: '-') ?></p>
                                    <p class="text-xs text-secondary mb-0"><?= format_file_size((int) $ev['file_size']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($evidences)) : ?>
                        <p class="text-secondary text-sm">Belum ada bukti pekerjaan. Upload lewat detail task di Checklist.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Komentar</h6>
            </div>
            <div class="card-body">
                <form action="<?= site_url('projects/' . $project['id'] . '/comment') ?>" method="post" class="mb-3">
                    <?= csrf_field() ?>
                    <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Tulis komentar..." required></textarea>
                    <button type="submit" class="btn btn-sm bg-gradient-primary mb-0">Kirim</button>
                </form>
                <?php foreach ($comments as $c) : ?>
                    <div class="mb-3">
                        <p class="text-sm font-weight-bold mb-0"><?= esc($c['fullname'] ?? 'User') ?></p>
                        <p class="text-sm mb-0"><?= esc($c['comment']) ?></p>
                        <p class="text-xs text-secondary mb-0"><?= date('d M Y H:i', strtotime($c['created_at'])) ?></p>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($comments)) : ?>
                    <p class="text-secondary text-sm">Belum ada komentar.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">PIC & Member</h6>
            </div>
            <div class="card-body">
                <form action="<?= site_url('projects/' . $project['id'] . '/member/add') ?>" method="post" class="mb-3">
                    <?= csrf_field() ?>
                    <select name="user_id" class="form-control mb-2" required>
                        <option value="">-- Pilih User --</option>
                        <?php foreach ($availableUsers as $u) : ?>
                            <option value="<?= $u['user_id'] ?>"><?= esc($u['fullname']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="role" class="form-control mb-2" required>
                        <option value="PIC">PIC</option>
                        <option value="Reviewer">Reviewer</option>
                    </select>
                    <button type="submit" class="btn btn-sm bg-gradient-primary w-100 mb-0">Tambah Member</button>
                </form>

                <?php foreach ($members as $m) : ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <p class="text-sm mb-0"><?= esc($m['fullname'] ?? 'User') ?></p>
                            <span class="badge bg-gradient-secondary"><?= esc($m['role']) ?></span>
                        </div>
                        <form action="<?= site_url('projects/' . $project['id'] . '/member/' . $m['id'] . '/remove') ?>" method="post" onsubmit="return confirm('Hapus member ini?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-link text-danger px-2 mb-0"><i class="ni ni-fat-remove"></i></button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($members)) : ?>
                    <p class="text-secondary text-sm">Belum ada member.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">Milestone</h6>
            </div>
            <div class="card-body">
                <form action="<?= site_url('projects/' . $project['id'] . '/milestone/add') ?>" method="post" class="mb-3">
                    <?= csrf_field() ?>
                    <input type="text" name="title" class="form-control mb-2" placeholder="Judul milestone" required>
                    <input type="date" name="target_date" class="form-control mb-2">
                    <button type="submit" class="btn btn-sm bg-gradient-primary w-100 mb-0">Tambah Milestone</button>
                </form>

                <?php foreach ($milestones as $ms) : ?>
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div class="d-flex align-items-start">
                            <form action="<?= site_url('projects/' . $project['id'] . '/milestone/' . $ms['id'] . '/toggle') ?>" method="post" class="me-2">
                                <?= csrf_field() ?>
                                <input type="checkbox" class="form-check-input mt-1" onchange="this.form.submit()" <?= $ms['is_done'] ? 'checked' : '' ?>>
                            </form>
                            <div>
                                <p class="text-sm mb-0 <?= $ms['is_done'] ? 'text-decoration-line-through text-secondary' : '' ?>"><?= esc($ms['title']) ?></p>
                                <span class="text-xs <?= ($ms['is_overdue'] ?? false) ? 'text-danger' : 'text-secondary' ?>">
                                    <?= $ms['target_date'] ? date('d M Y', strtotime($ms['target_date'])) : '-' ?>
                                    <?= ($ms['is_overdue'] ?? false) ? ' (Overdue)' : '' ?>
                                </span>
                            </div>
                        </div>
                        <form action="<?= site_url('projects/' . $project['id'] . '/milestone/' . $ms['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Hapus milestone ini?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-link text-danger px-1 mb-0"><i class="ni ni-fat-remove"></i></button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($milestones)) : ?>
                    <p class="text-secondary text-sm">Belum ada milestone.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card mt-4">
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