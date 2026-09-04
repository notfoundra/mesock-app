<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success text-white"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Daftar User</h6>
    </div>
    <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th class="text-xs text-secondary text-uppercase">Username</th>
                        <th class="text-xs text-secondary text-uppercase">Email</th>
                        <th class="text-xs text-secondary text-uppercase">Nama Lengkap</th>
                        <th class="text-xs text-secondary text-uppercase">Tim</th>
                        <th class="text-xs text-secondary text-uppercase">Akun</th>
                        <th class="text-xs text-secondary text-uppercase text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u) : ?>
                        <tr>
                            <td class="text-sm"><?= esc($u['username'] ?? '-') ?></td>
                            <td class="text-sm"><?= esc($u['email'] ?? '-') ?></td>
                            <td class="text-sm"><?= esc($u['fullname'] ?? '-') ?></td>
                            <td class="text-sm">
                                <?php if (($u['team_code'] ?? null) === 'IE') : ?>
                                    <span class="badge bg-gradient-primary">IE (Super Akses)</span>
                                <?php else : ?>
                                    <?= esc($u['team_name'] ?? '-') ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-sm">
                                <?= $u['active'] ? '<span class="badge bg-gradient-success">Aktif</span>' : '<span class="badge bg-gradient-secondary">Nonaktif</span>' ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= site_url('users/' . $u['id'] . '/edit') ?>" class="btn btn-link text-dark px-2 mb-0">
                                    <i class="ni ni-ruler-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>